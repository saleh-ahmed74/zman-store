<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Models\StorePage;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ContentPage extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render(): View
    {
        $page = StorePage::query()->where('slug', $this->slug)->firstOrFail();

        return view('livewire.storefront.content-page', compact('page'))->layout('layouts.store');
    }
}
