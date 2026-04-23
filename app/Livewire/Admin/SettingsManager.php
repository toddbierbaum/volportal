<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;

class SettingsManager extends Component
{
    public bool $requireApprovalBeforeOpportunities = false;
    public string $googleAnalyticsCode = '';
    public string $flash = '';

    public function mount(): void
    {
        $this->requireApprovalBeforeOpportunities = (bool) Setting::get('require_approval_before_opportunities', false);
        $this->googleAnalyticsCode = (string) Setting::get('google_analytics_code', '');
    }

    public function save(): void
    {
        $this->validate([
            'googleAnalyticsCode' => ['nullable', 'regex:/^G-[A-Z0-9]+$/'],
        ]);
        Setting::set('require_approval_before_opportunities', $this->requireApprovalBeforeOpportunities);
        Setting::set('google_analytics_code', $this->googleAnalyticsCode);
        $this->flash = 'Settings saved.';
    }

    public function render()
    {
        return view('livewire.admin.settings-manager');
    }
}
