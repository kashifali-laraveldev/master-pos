<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use OpenApi\Annotations as OA;

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/dashboard/stats",
     *   tags={"Dashboard"},
     *   summary="Get dashboard stats",
     *   description="Returns dashboard KPIs such as today sales, total products, low stock count, and revenue.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index()
    {
        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');
        $yesterday = now()->subDay()->toDateString();
        $lastMonth = now()->subMonth()->format('Y-m');

        $todaySales = Sale::where('status', 'completed')->whereDate('sold_at', $today);
        $yesterdaySales = Sale::where('status', 'completed')->whereDate('sold_at', $yesterday);
        $monthSales = Sale::where('status', 'completed')
            ->whereRaw("DATE_FORMAT(sold_at, '%Y-%m') = ?", [$thisMonth]);
        $lastMonthSales = Sale::where('status', 'completed')
            ->whereRaw("DATE_FORMAT(sold_at, '%Y-%m') = ?", [$lastMonth]);

        // Daily trend for the last 14 days.
        $dailyData = Sale::where('status', 'completed')
            ->whereBetween('sold_at', [now()->subDays(13)->startOfDay(), now()->endOfDay()])
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

        $paymentBreakdown = Sale::where('status', 'completed')
            ->whereBetween('sold_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->selectRaw('payment_method, COUNT(*) as tx_count, SUM(total_amount) as total_revenue')
            ->groupBy('payment_method')
            ->orderByDesc('total_revenue')
            ->get();

        $recentSales = Sale::with('user:id,name')
            ->where('status', 'completed')
            ->latest('sold_at')
            ->take(8)
            ->get(['id', 'invoice_number', 'user_id', 'total_amount', 'payment_method', 'sold_at']);

        $lowStockProducts = Product::where('is_active', true)
            ->whereRaw('stock_quantity <= low_stock_alert')
            ->orderByRaw('(low_stock_alert - stock_quantity) DESC')
            ->take(8)
            ->get(['id', 'name', 'stock_quantity', 'low_stock_alert', 'unit_label']);

        $todayRevenue = (float) $todaySales->sum('total_amount');
        $yesterdayRevenue = (float) $yesterdaySales->sum('total_amount');
        $monthRevenue = (float) $monthSales->sum('total_amount');
        $lastMonthRevenue = (float) $lastMonthSales->sum('total_amount');

        return response()->json([
            'today' => [
                'sales_count' => $todaySales->count(),
                'revenue' => $todayRevenue,
                'yesterday_revenue' => $yesterdayRevenue,
                'growth_percent' => $yesterdayRevenue > 0
                    ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 2)
                    : null,
            ],
            'month' => [
                'sales_count' => $monthSales->count(),
                'revenue' => $monthRevenue,
                'last_month_revenue' => $lastMonthRevenue,
                'growth_percent' => $lastMonthRevenue > 0
                    ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2)
                    : null,
            ],
            'low_stock_count' => Product::whereRaw('stock_quantity <= low_stock_alert')->where('is_active', true)->count(),
            'total_products' => Product::where('is_active', true)->count(),
            'total_sales_count' => Sale::where('status', 'completed')->count(),
            'avg_ticket' => Sale::where('status', 'completed')->avg('total_amount') ?? 0,
            'daily_chart' => $dailyData,
            'top_products' => $topProducts,
            'payment_breakdown' => $paymentBreakdown,
            'recent_sales' => $recentSales,
            'low_stock_products' => $lowStockProducts,
        ]);
    }
}

