<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) ($request->query('days', 14));
        $days = max(7, min($days, 30)); // clamp

        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        $todaySales = Sale::where('status', 'completed')->whereDate('sold_at', $today);
        $monthSales = Sale::where('status', 'completed')
            ->whereRaw("DATE_FORMAT(sold_at, '%Y-%m') = ?", [$thisMonth]);

        $from = now()->subDays($days - 1)->startOfDay();

        $dailyData = Sale::where('status', 'completed')
            ->whereBetween('sold_at', [$from, now()->endOfDay()])
            ->selectRaw("DATE(sold_at) as date, COUNT(*) as count, SUM(total_amount) as revenue")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereRaw("DATE_FORMAT(sales.sold_at, '%Y-%m') = ?", [$thisMonth])
            ->selectRaw('product_id, product_name, unit_label, SUM(quantity) as total_qty, SUM(total_price) as total_revenue')
            ->groupBy('product_id', 'product_name', 'unit_label')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        $paymentBreakdown = Sale::selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$from, now()->endOfDay()])
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        return response()->json([
            'today' => [
                'sales_count' => $todaySales->count(),
                'revenue' => $todaySales->sum('total_amount'),
            ],
            'month' => [
                'sales_count' => $monthSales->count(),
                'revenue' => $monthSales->sum('total_amount'),
            ],
            'low_stock_count' => Product::whereRaw('stock_quantity <= low_stock_alert')->where('is_active', true)->count(),
            'daily_chart' => $dailyData,
            'top_products' => $topProducts,
            'payment_breakdown' => $paymentBreakdown,
        ]);
    }
}

