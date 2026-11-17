<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class DashboardAnalytics
{
    /** @return array<string, mixed> */
    public function forPeriod(string $from, string $to, bool $includeGovernorates): array
    {
        $key = 'admin.analytics.'.hash('sha256', $from.'|'.$to.'|'.(int) $includeGovernorates);

        return Cache::remember($key, now()->addSeconds(60), function () use ($from, $to, $includeGovernorates): array {
            $dailyGroups = DB::table('orders')->selectRaw('status, payment_method, payment_status, DATE(placed_at) as day, COUNT(*) as order_count, SUM(total) as total_sum, SUM(shipping) as shipping_sum')
                ->whereBetween('placed_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->groupBy('status', 'payment_method', 'payment_status', DB::raw('DATE(placed_at)'))->get();

            if ($dailyGroups->isEmpty()) {
                return ['empty' => true, 'statuses' => [], 'payments' => [], 'daily' => [], 'expected' => 0.0, 'actual' => 0.0, 'cancelled_loss' => 0.0, 'refund_loss' => 0.0, 'aov' => 0.0, 'cancel_rate' => 0.0, 'return_rate' => 0.0, 'delivery_rate' => 0.0, 'awaiting_review' => 0, 'top_products' => [], 'top_governorates' => []];
            }

            $statuses = [];
            $payments = [];
            $daily = [];
            $expected = 0.0;
            $actual = 0.0;
            $cancelledLoss = 0.0;
            $deliveredCount = 0;
            $cancelledCount = 0;
            $awaitingReview = 0;
            foreach ($dailyGroups as $group) {
                $status = (string) $group->status;
                $method = (string) $group->payment_method;
                $day = (string) $group->day;
                $count = (int) $group->order_count;
                $sum = (float) $group->total_sum;
                $statuses[$status] = ($statuses[$status] ?? 0) + $count;
                $payments[$method]['count'] = ($payments[$method]['count'] ?? 0) + $count;
                $payments[$method]['total'] = ($payments[$method]['total'] ?? 0.0) + $sum;
                $daily[$day]['expected'] = ($daily[$day]['expected'] ?? 0.0) + (in_array($status, ['pending', 'confirmed', 'shipped'], true) || $group->payment_status === 'awaiting_review' ? $sum : 0.0);
                $daily[$day]['actual'] = ($daily[$day]['actual'] ?? 0.0) + ($status === 'delivered' && ($method === 'cod' || $group->payment_status === 'paid') ? $sum : 0.0);
                if (in_array($status, ['pending', 'confirmed', 'shipped'], true) || $group->payment_status === 'awaiting_review') {
                    $expected += $sum;
                }
                if ($status === 'delivered' && ($method === 'cod' || $group->payment_status === 'paid')) {
                    $actual += $sum;
                    $deliveredCount += $count;
                }
                if ($status === 'cancelled') {
                    $cancelledLoss += $sum;
                    $cancelledCount += $count;
                }
                if ($group->payment_status === 'awaiting_review') {
                    $awaitingReview += $count;
                }
            }

            $refundTotals = DB::table('return_requests')->join('orders', 'orders.id', '=', 'return_requests.order_id')
                ->where('return_requests.status', 'refunded')->whereBetween('orders.placed_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->selectRaw('COUNT(*) as refund_count, SUM(refund_amount) as refund_sum')->first();
            $refundLoss = (float) ($refundTotals->refund_sum ?? 0);
            $topProducts = DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', 'delivered')->whereBetween('orders.placed_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->selectRaw('order_items.product_name_ar, order_items.product_name_en, SUM(order_items.quantity) as quantity, SUM(order_items.line_total) as revenue')
                ->groupBy('order_items.product_name_ar', 'order_items.product_name_en')->orderByDesc('quantity')->limit(5)->get()->toArray();
            $topGovernorates = $includeGovernorates
                ? DB::table('orders')->whereNotNull('governorate_id')->where('status', 'delivered')->whereBetween('placed_at', [$from.' 00:00:00', $to.' 23:59:59'])
                    ->selectRaw('governorate_name, COUNT(*) as order_count, SUM(total) as revenue')->groupBy('governorate_name')->orderByDesc('order_count')->limit(5)->get()->toArray()
                : [];

            $totalOrders = array_sum($statuses);

            return [
                'empty' => false, 'statuses' => $statuses, 'payments' => $payments, 'daily' => $daily,
                'expected' => round($expected, 2), 'actual' => round(max(0, $actual - $refundLoss), 2),
                'cancelled_loss' => round($cancelledLoss, 2), 'refund_loss' => round($refundLoss, 2), 'refund_count' => (int) ($refundTotals->refund_count ?? 0),
                'aov' => $deliveredCount === 0 ? 0.0 : round($actual / $deliveredCount, 2),
                'cancel_rate' => $totalOrders === 0 ? 0.0 : round($cancelledCount * 100 / $totalOrders, 1),
                'return_rate' => $deliveredCount === 0 ? 0.0 : round(($statuses['return_requested'] ?? 0) * 100 / $deliveredCount, 1),
                'delivery_rate' => $totalOrders === 0 ? 0.0 : round($deliveredCount * 100 / $totalOrders, 1),
                'awaiting_review' => $awaitingReview, 'top_products' => $topProducts, 'top_governorates' => $topGovernorates,
            ];
        });
    }
}
