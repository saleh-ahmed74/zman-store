<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class RequestReturn
{
    public function handle(Order $order, User $user, string $reason): ReturnRequest
    {
        return DB::transaction(function () use ($order, $user, $reason): ReturnRequest {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->user_id !== $user->id) {
                throw new AccessDeniedHttpException;
            }
            if ($locked->status !== 'delivered' || $locked->delivered_at === null || $locked->delivered_at->lt(now()->subDays(14))) {
                throw new HttpException(422, __('store.return_window_closed'));
            }
            if (ReturnRequest::query()->where('order_id', $locked->id)->whereIn('status', ['requested', 'approved', 'received'])->exists()) {
                throw new HttpException(422, __('store.return_already_requested'));
            }

            $request = ReturnRequest::query()->create(['order_id' => $locked->id, 'user_id' => $user->id, 'reason' => $reason, 'status' => 'requested']);
            $locked->update(['status' => 'return_requested']);
            OrderStatusLog::query()->create(['order_id' => $locked->id, 'from_status' => 'delivered', 'to_status' => 'return_requested', 'changed_by' => $user->id, 'note' => 'Return requested']);

            return $request;
        }, 3);
    }
}
