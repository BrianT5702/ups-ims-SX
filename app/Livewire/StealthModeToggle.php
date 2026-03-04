<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StealthModeToggle extends Component
{
    public $isActive = false;
    public $lastChanged = null;
    public $changedBy = null;

    /** @var string|null Set when DB/table is unavailable */
    public $loadError = null;

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

        $this->loadError = null;

        try {
            // Load stealth mode status from database
            $setting = DB::connection('ups')->table('stealth_settings')
                ->where('key', 'transaction_stealth_mode')
                ->first();

            $this->isActive = $setting ? (bool) $setting->value : false;
            $this->lastChanged = $setting ? $setting->updated_at : null;
            $this->changedBy = $setting ? $setting->changed_by_user_id : null;
        } catch (\Throwable $e) {
            Log::error('StealthModeToggle: Failed to load stealth settings', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->loadError = 'Stealth settings are not available. The stealth_settings table may not exist. Please run migrations on production: php artisan migrate --force --database=ups';
            $this->isActive = false;
            $this->lastChanged = null;
            $this->changedBy = null;
        }
    }

    public function toggle()
    {
        // Double-check authorization
        if (!auth()->check() || !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Access denied. Super Admin only.');
        }

        if ($this->loadError) {
            session()->flash('error', 'Cannot toggle stealth mode: ' . $this->loadError);
            return;
        }

        $this->isActive = !$this->isActive;

        try {
            $existingCreatedAt = DB::connection('ups')->table('stealth_settings')
                ->where('key', 'transaction_stealth_mode')
                ->value('created_at');

            // Save to database
            DB::connection('ups')->table('stealth_settings')->updateOrInsert(
                ['key' => 'transaction_stealth_mode'],
                [
                    'value' => $this->isActive ? '1' : '0',
                    'changed_by_user_id' => auth()->id(),
                    'updated_at' => now(),
                    'created_at' => $existingCreatedAt ?? now(),
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
        } catch (\Throwable $e) {
            Log::error('StealthModeToggle: Failed to save stealth settings', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->loadStatus();
            session()->flash('error', 'Failed to save stealth mode. ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.stealth-mode-toggle')->layout('layouts.app');
    }
}

