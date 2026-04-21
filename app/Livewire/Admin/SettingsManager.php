<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;

class SettingsManager extends Component
{
    public bool $requireApprovalBeforeOpportunities = false;
    public string $flash = '';

    public function mount(): void
    {
        $this->requireApprovalBeforeOpportunities = (bool) Setting::get('require_approval_before_opportunities', false);
    }

    public function save(): void
    {
        Setting::set('require_approval_before_opportunities', $this->requireApprovalBeforeOpportunities);
        $this->flash = 'Settings saved.';
    }

    public function render()
    {
        return view('livewire.admin.settings-manager');
    }
}
