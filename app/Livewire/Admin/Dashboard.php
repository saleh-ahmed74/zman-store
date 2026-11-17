<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\DashboardAnalytics;
use App\Services\StoreSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Dashboard extends Component
{
    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function period(int $days): void
    {
        abort_unless(in_array($days, [1, 7, 30], true), 422);
        $this->dateFrom = now()->subDays($days - 1)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function refreshMetrics(): void
    {
        $includeGovernorates = ! (bool) app(StoreSettings::class)->get('store.shipping.free_for_all', false);
        Cache::forget('admin.analytics.'.hash('sha256', $this->dateFrom.'|'.$this->dateTo.'|'.(int) $includeGovernorates));
    }

    public function render(DashboardAnalytics $analytics): View
    {
        $validated = validator(['dateFrom' => $this->dateFrom, 'dateTo' => $this->dateTo], [
            'dateFrom' => ['required', 'date'], 'dateTo' => ['required', 'date', 'after_or_equal:dateFrom'],
        ])->validate();
        if (Carbon::parse($validated['dateTo'])->diffInDays(Carbon::parse($validated['dateFrom'])) > 365) {
            throw ValidationException::withMessages(['dateTo' => __('store.analytics_max_range')]);
        }
        $includeGovernorates = ! (bool) app(StoreSettings::class)->get('store.shipping.free_for_all', false);
        $metrics = $analytics->forPeriod($this->dateFrom, $this->dateTo, $includeGovernorates);

        return view('livewire.admin.dashboard', compact('metrics', 'includeGovernorates'))->layout('layouts.admin', ['title' => __('store.dashboard')]);
    }
}
