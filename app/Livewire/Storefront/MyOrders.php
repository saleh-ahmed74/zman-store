<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Actions\CancelOrder;
use App\Actions\RequestReturn;
use App\Models\Order;
use App\Models\User;
use App\Services\StoreSettings;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class MyOrders extends Component
{
    use WithPagination;

    public ?int $returningOrderId = null;

    public string $returnReason = '';

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        if (app(StoreSettings::class)->get('store.auth.require_email_verification', false) && ! $user->hasVerifiedEmail()) {
            $this->redirectRoute('verification.notice');
        }
    }

    public function cancel(int $orderId, CancelOrder $action): void
    {
        $order = Order::query()->where('user_id', auth()->id())->findOrFail($orderId);
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $action->handle($order, $user);
        session()->flash('toast', __('store.order_cancelled'));
    }

    public function requestReturn(RequestReturn $action): void
    {
        $validated = $this->validate(['returnReason' => ['required', 'string', 'min:5', 'max:2000'], 'returningOrderId' => ['required', 'integer']]);
        $order = Order::query()->where('user_id', auth()->id())->findOrFail($validated['returningOrderId']);
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $action->handle($order, $user, $validated['returnReason']);
        $this->reset(['returnReason', 'returningOrderId']);
        session()->flash('toast', __('store.return_requested'));
    }

    public function render(): View
    {
        $orders = Order::query()->with('items')->where('user_id', auth()->id())->latest('placed_at')->paginate(10);

        return view('livewire.storefront.my-orders', compact('orders'))->layout('layouts.store', ['title' => __('store.my_orders')]);
    }
}
