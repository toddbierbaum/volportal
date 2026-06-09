<?php

namespace App\Console\Commands;

use App\Models\Signup;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('users:merge {email : Email shared by the two duplicate User rows} {--keeper= : ID of the row to keep (skips the interactive prompt)} {--dry-run : Preview without making changes}')]
#[Description('Consolidate two User rows that share the same email into one: re-parent signups, union categories, OR-merge opt-ins, then delete the duplicate. Run with --dry-run first.')]
class MergeUsers extends Command
{
    // Higher = preferred when both users have a signup for the same position.
    private const STATUS_PRIORITY = [
        'attended'   => 5,
        'confirmed'  => 4,
        'waitlisted' => 3,
        'pending'    => 2,
        'cancelled'  => 1,
        'no_show'    => 0,
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $keeperOpt = $this->option('keeper');

        $email = strtolower(trim((string) $this->argument('email')));
        $rows = User::where('email', $email)->orderBy('id')->get();

        if ($rows->isEmpty()) {
            $this->error("No users found with email {$email}.");
            return self::FAILURE;
        }
        if ($rows->count() === 1) {
            $this->error("Only one user has email {$email} — nothing to merge.");
            return self::FAILURE;
        }
        if ($rows->count() > 2) {
            $this->error("More than 2 users share this email. Pass --keeper=ID and re-run for each duplicate:");
            foreach ($rows as $r) $this->line('  '.sprintf('user#%-6d role=%-9s name=%s', $r->id, $r->role, $r->name ?: '(no name)'));
            return self::FAILURE;
        }

        [$keeper, $duplicate] = $this->pickKeeper($rows, $keeperOpt);
        if (! $keeper) {
            return self::FAILURE; // pickKeeper already printed why
        }

        $this->summarize($keeper, 'KEEP  ');
        $this->summarize($duplicate, 'DELETE');
        $this->newLine();
        $this->planSignupMoves($keeper, $duplicate);
        $this->planCategoryMoves($keeper, $duplicate);
        $this->planFlagMerges($keeper, $duplicate);

        if ($dryRun) {
            $this->newLine();
            $this->info('(dry-run) no changes made.');
            return self::SUCCESS;
        }

        $this->newLine();
        if (! $this->confirm("Merge {$duplicate->email} (#{$duplicate->id}) into {$keeper->email} (#{$keeper->id}) and DELETE the duplicate?", false)) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        $duplicateId = $duplicate->id;
        DB::transaction(function () use ($keeper, $duplicate) {
            $this->mergeSignups($keeper, $duplicate);
            $this->mergeCategories($keeper, $duplicate);
            $this->mergeUserFlags($keeper, $duplicate);
            $duplicate->refresh()->delete();
        });

        $this->info("Merged. Kept user#{$keeper->id} ({$keeper->email}). Duplicate user#{$duplicateId} deleted.");
        return self::SUCCESS;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $rows  Exactly two users sharing an email.
     * @return array{0: ?User, 1: ?User}  [$keeper, $duplicate] or [null, null] on bad input.
     */
    private function pickKeeper($rows, mixed $keeperOpt): array
    {
        [$a, $b] = [$rows[0], $rows[1]];

        if ($keeperOpt !== null && $keeperOpt !== '') {
            $id = (int) $keeperOpt;
            if ($id === $a->id) return [$a, $b];
            if ($id === $b->id) return [$b, $a];
            $this->error("--keeper={$keeperOpt} doesn't match either user (#{$a->id} or #{$b->id}).");
            return [null, null];
        }

        $this->line('Two users share this email:');
        $this->line('  1) '.$this->shortDescribe($a));
        $this->line('  2) '.$this->shortDescribe($b));
        $pick = $this->choice('Which is the keeper?', ['1', '2'], '1');
        return $pick === '1' ? [$a, $b] : [$b, $a];
    }

    private function shortDescribe(User $u): string
    {
        $signups = Signup::where('user_id', $u->id)->count();
        $hours = Signup::where('user_id', $u->id)->sum('hours_worked');
        return sprintf(
            'user#%d  role=%s  name=%s  signups=%d  hours=%s  created=%s',
            $u->id,
            $u->role,
            $u->name ?: '(no name)',
            $signups,
            $hours ?: '0',
            $u->created_at?->toDateString() ?? '?'
        );
    }

    private function summarize(User $u, string $label): void
    {
        $signups = Signup::where('user_id', $u->id);
        $count = (clone $signups)->count();
        $hours = (clone $signups)->sum('hours_worked');
        $cats = $u->categories()->count();
        $this->line(sprintf(
            '%s  user#%d  %s  <%s>  role=%s  signups=%d  hours=%s  categories=%d  approved=%s',
            $label,
            $u->id,
            $u->name ?: '(no name)',
            $u->email,
            $u->role,
            $count,
            $hours ?: '0',
            $cats,
            $u->approved_at?->toDateString() ?? '(pending)'
        ));
    }

    private function planSignupMoves(User $keeper, User $duplicate): void
    {
        $dupSignups = Signup::where('user_id', $duplicate->id)->get();
        if ($dupSignups->isEmpty()) {
            $this->line('  signups: none on duplicate.');
            return;
        }
        foreach ($dupSignups as $s) {
            $clash = Signup::where('user_id', $keeper->id)
                ->where('position_id', $s->position_id)
                ->first();
            if (! $clash) {
                $this->line("  signup#{$s->id} (position#{$s->position_id} status={$s->status}) → reparent to keeper");
                continue;
            }
            $winner = $this->preferred($clash, $s);
            $loser = $winner === $clash ? $s : $clash;
            $this->line(sprintf(
                '  position#%d collision: keep signup#%d (status=%s), drop signup#%d (status=%s), sum hours',
                $s->position_id,
                $winner->id,
                $winner->status,
                $loser->id,
                $loser->status,
            ));
        }
    }

    private function mergeSignups(User $keeper, User $duplicate): void
    {
        $dupSignups = Signup::where('user_id', $duplicate->id)->get();
        foreach ($dupSignups as $s) {
            $clash = Signup::where('user_id', $keeper->id)
                ->where('position_id', $s->position_id)
                ->first();
            if (! $clash) {
                $s->user_id = $keeper->id;
                $s->save();
                continue;
            }
            $winner = $this->preferred($clash, $s);
            $loser = $winner === $clash ? $s : $clash;
            $winner->hours_worked = (float) ($winner->hours_worked ?? 0) + (float) ($loser->hours_worked ?? 0);
            if ($winner->user_id !== $keeper->id) {
                $winner->user_id = $keeper->id;
            }
            $winner->save();
            $loser->delete();
        }
    }

    private function preferred(Signup $a, Signup $b): Signup
    {
        $pa = self::STATUS_PRIORITY[$a->status] ?? -1;
        $pb = self::STATUS_PRIORITY[$b->status] ?? -1;
        if ($pa !== $pb) {
            return $pa > $pb ? $a : $b;
        }
        // Tie-break: row with logged hours first, then most-recently updated.
        if (($a->hours_worked !== null) !== ($b->hours_worked !== null)) {
            return $a->hours_worked !== null ? $a : $b;
        }
        return $a->updated_at >= $b->updated_at ? $a : $b;
    }

    private function planCategoryMoves(User $keeper, User $duplicate): void
    {
        $dupCats = $duplicate->categories()->pluck('categories.id')->all();
        $keeperCats = $keeper->categories()->pluck('categories.id')->all();
        $toAdd = array_values(array_diff($dupCats, $keeperCats));
        if (empty($toAdd)) {
            $this->line('  categories: nothing new to copy.');
            return;
        }
        $this->line('  categories: copy ids '.implode(',', $toAdd).' to keeper.');
    }

    private function mergeCategories(User $keeper, User $duplicate): void
    {
        $dupCats = $duplicate->categories()->pluck('categories.id')->all();
        $keeper->categories()->syncWithoutDetaching($dupCats);
        $duplicate->categories()->detach();
    }

    private function planFlagMerges(User $keeper, User $duplicate): void
    {
        $notes = [];
        if ($duplicate->sms_opt_in && ! $keeper->sms_opt_in) $notes[] = 'sms_opt_in: false→true';
        if ($duplicate->opportunity_alerts_opt_in && ! $keeper->opportunity_alerts_opt_in) $notes[] = 'opportunity_alerts_opt_in: false→true';
        if (! $keeper->phone && $duplicate->phone) $notes[] = "phone: (none)→{$duplicate->phone}";

        foreach (['approved_at','background_check_acknowledged_at','age_certified_at','background_check_verified_at','age_verified_at'] as $col) {
            $k = $keeper->{$col};
            $d = $duplicate->{$col};
            if ($d && (! $k || $d < $k)) {
                $notes[] = $col.': '.($k?->toDateString() ?? '(null)').'→'.$d->toDateString();
            }
        }
        if (empty($notes)) {
            $this->line('  user flags: keeper already has everything.');
            return;
        }
        foreach ($notes as $n) $this->line('  user flag '.$n);
    }

    private function mergeUserFlags(User $keeper, User $duplicate): void
    {
        $changed = false;
        if ($duplicate->sms_opt_in && ! $keeper->sms_opt_in) { $keeper->sms_opt_in = true; $changed = true; }
        if ($duplicate->opportunity_alerts_opt_in && ! $keeper->opportunity_alerts_opt_in) { $keeper->opportunity_alerts_opt_in = true; $changed = true; }
        if (! $keeper->phone && $duplicate->phone) { $keeper->phone = $duplicate->phone; $changed = true; }

        foreach (['approved_at','background_check_acknowledged_at','age_certified_at','background_check_verified_at','age_verified_at'] as $col) {
            $k = $keeper->{$col};
            $d = $duplicate->{$col};
            if ($d && (! $k || $d < $k)) { $keeper->{$col} = $d; $changed = true; }
        }
        if ($changed) $keeper->save();
    }
}
