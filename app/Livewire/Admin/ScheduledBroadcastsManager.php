<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;

class ScheduledBroadcastsManager extends Component
{
    public bool $opportunityAlertsEnabled = true;
    public string $flash = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $this->opportunityAlertsEnabled = (bool) Setting::get('opportunity_alerts_enabled', true);
    }

    public function toggleOpportunityAlerts(): void
    {
        $this->opportunityAlertsEnabled = ! $this->opportunityAlertsEnabled;
        Setting::set('opportunity_alerts_enabled', $this->opportunityAlertsEnabled);
        $this->flash = $this->opportunityAlertsEnabled
            ? 'Monthly opportunity alerts enabled.'
            : 'Monthly opportunity alerts disabled.';
    }

    public function render()
    {
        return view('livewire.admin.scheduled-broadcasts-manager');
    }
}
