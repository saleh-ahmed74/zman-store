<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public string $paymentFilter = '';

    public string $refundAmount = '';

    public function changeStatus(int $orderId, string $toStatus): void
    {
        validator(['status' => $toStatus], ['status' => ['required', Rule::in(['confirmed', 'shipped', 'delivered', 'cancelled'])]])->validate();
        $admin = auth()->user();
        abort_unless($admin instanceof User && $admin->is_admin, 403);

        DB::transaction(function () use ($orderId, $toStatus, $admin): void {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            $allowed = match ($order->status) {
                'pending' => ['confirmed', 'cancelled'], 'confirmed' => ['shipped', 'cancelled'], 'shipped' => ['delivered'], default => [],
            };
            abort_unless(in_array($toStatus, $allowed, true), 422, __('store.invalid_order_transition'));
            $timestamp = match ($toStatus) {
                'confirmed' => 'confirmed_at', 'shipped' => 'shipped_at', 'delivered' => 'delivered_at', 'cancelled' => 'cancelled_at'
            };
            $fromStatus = $order->status;
            $order->update(['status' => $toStatus, $timestamp => now()]);
            OrderStatusLog::query()->create(['order_id' => $order->id, 'from_status' => $fromStatus, 'to_status' => $toStatus, 'changed_by' => $admin->id, 'note' => 'Admin status update']);
        }, 3);
    }

    public function reviewPayment(int $orderId, string $decision): void
    {
        validator(['decision' => $decision], ['decision' => ['required', Rule::in(['paid', 'rejected'])]])->validate();
        $admin = auth()->user();
        abort_unless($admin instanceof User && $admin->is_admin, 403);
        $order = Order::query()->findOrFail($orderId);
        abort_unless($order->payment_status === 'awaiting_review' && $order->payment_method !== 'cod', 422);
        $order->update(['payment_status' => $decision, 'payment_reviewed_by' => $admin->id, 'payment_reviewed_at' => now()]);
        DB::table('admin_audits')->insert(['user_id' => $admin->id, 'action' => 'order.payment_reviewed', 'subject_type' => Order::class, 'subject_id' => $order->id, 'old_values' => json_encode(['payment_status' => 'awaiting_review']), 'new_values' => json_encode(['payment_status' => $decision]), 'ip_address' => request()->ip(), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function resolveReturn(int $requestId, string $decision): void
    {
        $validated = validator(['decision' => $decision, 'refundAmount' => $this->refundAmount], [
            'decision' => ['required', Rule::in(['approved', 'rejected', 'received', 'refunded'])],
            'refundAmount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ])->validate();
        $admin = auth()->user();
        abort_unless($admin instanceof User && $admin->is_admin, 403);
        $request = ReturnRequest::query()->with('order')->findOrFail($requestId);
        $allowed = match ($request->status) {
            'requested' => ['approved', 'rejected'], 'approved' => ['received'], 'received' => ['refunded'], default => [],
        };
        abort_unless(in_array($decision, $allowed, true), 422);
        DB::transaction(function () use ($request, $decision, $admin, $validated): void {
            $fromStatus = $request->status;
            $request->update([
                'status' => $decision,
                'resolved_at' => in_array($decision, ['rejected', 'refunded'], true) ? now() : null,
                'refund_amount' => $decision === 'refunded' ? (float) ($validated['refundAmount'] ?? 0) : $request->refund_amount,
            ]);
            if ($decision === 'rejected') {
                $request->order->update(['status' => 'delivered']);
                OrderStatusLog::query()->create(['order_id' => $request->order_id, 'from_status' => 'return_requested', 'to_status' => 'delivered', 'changed_by' => $admin->id, 'note' => 'Return request rejected']);
            }
            DB::table('admin_audits')->insert([
                'user_id' => $admin->id, 'action' => 'order.return_status_changed',
                'subject_type' => ReturnRequest::class, 'subject_id' => $request->id,
                'old_values' => json_encode(['status' => $fromStatus]), 'new_values' => json_encode(['status' => $decision]),
                'ip_address' => request()->ip(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }, 3);
    }

    public function render(): View
    {
        $orders = Order::query()->with('items')
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->paymentFilter !== '', fn ($query) => $query->where('payment_status', $this->paymentFilter))
            ->latest('placed_at')->paginate(20);
        $returns = ReturnRequest::query()->with(['order', 'user'])->whereIn('status', ['requested', 'approved', 'received'])->latest()->get();

        return view('livewire.admin.orders.index', compact('orders', 'returns'))->layout('layouts.admin', ['title' => __('store.manage_orders')]);
    }
}
