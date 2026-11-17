<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class CancelOrder
{
    public function handle(Order $order, User $user): void
    {
        DB::transaction(function () use ($order, $user): void {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->user_id !== $user->id) {
                throw new AccessDeniedHttpException;
            }
            if ($locked->status !== 'pending') {
                throw new HttpException(422, __('store.cancel_pending_only'));
            }

            $locked->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            OrderStatusLog::query()->create(['order_id' => $locked->id, 'from_status' => 'pending', 'to_status' => 'cancelled', 'changed_by' => $user->id, 'note' => 'Cancelled by customer']);
        }, 3);
    }
}
