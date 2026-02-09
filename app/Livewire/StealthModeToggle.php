<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StealthModeToggle extends Component
{
    public $isActive = false;
    public $lastChanged = null;
    public $changedBy = null;

    public function mount()
    {
        $this->loadStatus();
    }

    public function loadStatus()
    {
        // Check if user is Super Admin
        if (!auth()->check() || !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Access denied. Super Admin only.');
        }

        // Load stealth mode status from database
        $setting = DB::connection('ups')->table('stealth_settings')
            ->where('key', 'transaction_stealth_mode')
            ->first();

        $this->isActive = $setting ? (bool) $setting->value : false;
        $this->lastChanged = $setting ? $setting->updated_at : null;
        $this->changedBy = $setting ? $setting->changed_by_user_id : null;
    }

    public function toggle()
    {
        // Double-check authorization
        if (!auth()->check() || !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Access denied. Super Admin only.');
        }

        $this->isActive = !$this->isActive;

        // Save to database
        DB::connection('ups')->table('stealth_settings')->updateOrInsert(
            ['key' => 'transaction_stealth_mode'],
            [
                'value' => $this->isActive ? '1' : '0',
                'changed_by_user_id' => auth()->id(),
                'updated_at' => now(),
                'created_at' => DB::connection('ups')->table('stealth_settings')
                    ->where('key', 'transaction_stealth_mode')
                    ->value('created_at') ?? now()
            ]
        );

        // Clear cache to ensure changes take effect immediately
        Cache::forget('stealth_mode_active');
        
        // Also clear cache for all connections (UPS, URS, UCS, etc.)
        $connections = ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];
        foreach ($connections as $connection) {
            try {
                Cache::store('database')->forget("stealth_mode_active_{$connection}");
            } catch (\Exception $e) {
                // Ignore if cache store doesn't exist
            }
        }

        $this->loadStatus();

        session()->flash('message', $this->isActive 
            ? 'Stealth mode activated. Transaction data is now hidden from non-super-admin users.' 
            : 'Stealth mode deactivated. Transaction data is now visible to all authorized users.');
    }

    public function render()
    {
        return view('livewire.stealth-mode-toggle')->layout('layouts.app');
    }
}

