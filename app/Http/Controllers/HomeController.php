<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\announcements;
use App\Models\windowtables;
use App\Models\windowtrans;
use App\Models\rbostoretables;
use App\Models\rbotransactiontables;
use App\Models\rbotransactionsalestrans;
use App\Models\receivedordertrans;
use App\Models\wastedeclarationtrans;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Agent;
use Detection\MobileDetect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;

        // Replace direct DB statements with a safer, recent-only batched approach
        $this->cleanupRecentDuplicates();

        $announcements = announcements::select('*')->get();

        if (in_array($role, ['SUPERADMIN', 'ADMIN', 'OPIC'])) {
            $metrics = $this->calculateMetrics();

            Artisan::call('optimize:clear');

            \Log::info('Metrics being passed:', $metrics);

            return Inertia::render('Home/admin', [
                'metrics' => $metrics,
                'announcements' => $this->getAnnouncements(),
                'username' => auth()->user()->name,
            ]);
        } else {
            return Inertia::render('Home/stores', ['announcements' => $announcements]);
        }
    }

    /**
     * Clean up only recent duplicate records in very small batches
     * This is the safest approach to avoid timeouts while still maintaining data integrity
     */
    private function cleanupRecentDuplicates()
    {
        try {
            // Use a much smaller batch size for safety
            $batchSize = 100;
            
            // Only process records from the last 24 hours
            $recentDate = now()->subHours(24)->format('Y-m-d H:i:s');
            
            // Process sales transactions
            $affected = DB::affectingStatement("
                DELETE t1 FROM rbotransactionsalestrans t1
                JOIN rbotransactionsalestrans t2 
                ON t1.transactionid = t2.transactionid
                AND t1.linenum = t2.linenum
                AND t1.id > t2.id
                WHERE t1.createddate >= ?
                LIMIT ?
            ", [$recentDate, $batchSize]);
            
            \Log::info("Removed {$affected} recent duplicate sales transaction records");
            
            // Process transaction tables
            $affected = DB::affectingStatement("
                DELETE t1 FROM rbotransactiontables t1
                JOIN rbotransactiontables t2 
                ON t1.transactionid = t2.transactionid
                AND t1.id > t2.id
                WHERE t1.createddate >= ?
                LIMIT ?
            ", [$recentDate, $batchSize]);
            
            \Log::info("Removed {$affected} recent duplicate transaction records");
            
        } catch (\Exception $e) {
            \Log::error('Error removing recent duplicate records: ' . $e->getMessage());
        }
    }

    public function getMetrics(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'nullable|array',
                'stores.*' => 'string'  // Changed from strict validation to allow store names
            ]);

            $query = rbotransactiontables::whereDate('createddate', '>=', $validated['start_date'])
                ->whereDate('createddate', '<=', $validated['end_date']);

            if (!empty($validated['stores'])) {
                // Handle both object and string store names
                $storeNames = collect($validated['stores'])->map(function ($store) {
                    return is_array($store) ? $store['NAME'] : $store;
                })->toArray();
                
                $query->whereIn('store', $storeNames);
            }

            $metrics = $this->calculateMetrics($query);

            return response()->json($metrics);
        } catch (\Exception $e) {
            \Log::error('Metrics Retrieval Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Unable to retrieve metrics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getStores()
    {
        $stores = rbostoretables::select('NAME')
            ->orderBy('NAME')
            ->distinct()
            ->get();

        return response()->json($stores);
    }

    private function calculateMetrics($query = null)
    {
        // If no query provided, use last 30 days as default to ensure we have data
        if (!$query) {
            $query = rbotransactiontables::whereDate('createddate', '>=', now()->subDays(30))
                ->whereDate('createddate', '<=', now());
        }

        $baseMetrics = $query->select([
            DB::raw('COALESCE(SUM(grossamount), 0) as totalGross'),
            DB::raw('COALESCE(SUM(netamount), 0) as totalNetsales'),
            DB::raw('COALESCE(SUM(totaldiscamount), 0) as totalDiscount'),
            DB::raw('COALESCE(SUM(costamount), 0) as totalCost'),
            DB::raw('COALESCE(SUM(netamount - netamountnotincltax), 0) as totalVat'),
            DB::raw('COALESCE(SUM(netamountnotincltax), 0) as totalVatableSales'),
            DB::raw('COALESCE(SUM(cash), 0) as totalCash'),
            DB::raw('COALESCE(SUM(gcash), 0) as totalGcash'),
            DB::raw('COALESCE(SUM(paymaya), 0) as totalPaymaya'),
            DB::raw('COALESCE(SUM(card), 0) as totalCard'),
            DB::raw('COALESCE(SUM(loyaltycard), 0) as totalLoyaltyCard'),
            DB::raw('COALESCE(SUM(foodpanda), 0) as totalFoodPanda'),
            DB::raw('COALESCE(SUM(grabfood), 0) as totalGrabFood'),
            DB::raw('COALESCE(SUM(representation), 0) as totalrepresentation'),
        ])->first();

        $totalSales = $baseMetrics->totalNetsales ?: 1;

        // Extract date constraints from query or use last 30 days as default
        $startDate = now()->subDays(30);
        $endDate = now();
        
        $whereClause = $query->getQuery()->wheres ?? [];
        foreach ($whereClause as $where) {
            if (isset($where['column']) && $where['column'] === 'createddate') {
                if ($where['operator'] === '>=' && isset($where['value'])) {
                    $startDate = $where['value'];
                } elseif ($where['operator'] === '<=' && isset($where['value'])) {
                    $endDate = $where['value'];
                }
            }
        }

        $totalTransactions = rbotransactionsalestrans::whereDate('createddate', '>=', $startDate)
        ->whereDate('createddate', '<=', $endDate)
        ->count();

        $totalReceivedDeliveries = receivedordertrans::whereDate('TRANSDATE', '>=', $startDate)
            ->whereDate('TRANSDATE', '<=', $endDate)
            ->count();

        $totalWaste = wastedeclarationtrans::whereDate('TRANSDATE', '>=', $startDate)
            ->whereDate('TRANSDATE', '<=', $endDate)
            ->count();

        return [
            'totalGross' => (float) ($baseMetrics->totalGross ?? 0),
            'totalNetsales' => (float) ($baseMetrics->totalNetsales ?? 0),
            'totalDiscount' => (float) ($baseMetrics->totalDiscount ?? 0),
            'totalCost' => (float) ($baseMetrics->totalCost ?? 0),
            'totalVat' => (float) ($baseMetrics->totalVat ?? 0),
            'totalVatableSales' => (float) ($baseMetrics->totalVatableSales ?? 0),
            'totalTransactions' => $totalTransactions,
            'totalReceivedDeliveries' => $totalReceivedDeliveries,
            'totalWaste' => $totalWaste,
            'paymentBreakdown' => [
                'cash' => round($baseMetrics->totalCash),
                'gcash' => round($baseMetrics->totalGcash),
                'paymaya' => round($baseMetrics->totalPaymaya),
                'card' => round($baseMetrics->totalCard),
                'loyaltyCard' => round($baseMetrics->totalLoyaltyCard),
                'foodPanda' => round($baseMetrics->totalFoodPanda),
                'grabFood' => round($baseMetrics->totalGrabFood),
                'representation' => round($baseMetrics->totalrepresentation),
            ],
            'todayTransactions' => rbotransactiontables::whereDate('createddate', now()->today())->count(),
        ];
    }

    public function getTopBottomProducts(Request $request)
{
    try {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'stores' => 'sometimes|array',
            'stores.*' => 'string',
            'product_type' => 'sometimes|string|in:all,regular,non_product'
        ]);

        $productType = $validated['product_type'] ?? 'all';

        // Basic sales count
        $basicSalesCount = DB::table('rbotransactionsalestrans as r')
            ->whereDate('r.createddate', '>=', $validated['start_date'])
            ->whereDate('r.createddate', '<=', $validated['end_date'])
            ->count();
            
        \Log::info('Basic sales data count', [
            'count' => $basicSalesCount,
            'date_range' => [$validated['start_date'], $validated['end_date']]
        ]);

        // Query sales + department
        $query = DB::table('rbotransactionsalestrans as r')
            ->leftJoin('rboinventtables as ri', 'r.itemid', '=', 'ri.itemid')
            ->select(
                'r.itemname',
                'r.itemid',
                DB::raw('ABS(SUM(r.qty)) as total_quantity'),
                DB::raw('ABS(SUM(r.netamount)) as total_sales'),
                DB::raw('COUNT(DISTINCT r.store) as store_count'),
                DB::raw('COALESCE(ri.itemdepartment, "UNKNOWN") as retail_department'),
                // Map product category based on itemdepartment
                DB::raw('CASE 
                            WHEN ri.itemdepartment = "NON PRODUCT" THEN "NON PRODUCT"
                            ELSE "REGULAR PRODUCT"
                         END as product_category')
            )
            ->whereDate('r.createddate', '>=', $validated['start_date'])
            ->whereDate('r.createddate', '<=', $validated['end_date'])
            ->where(function($q) {
                $q->where('r.qty', '>', 0)
                  ->orWhere('r.netamount', '>', 0);
            });

        if (!empty($validated['stores'])) {
            $query->whereIn('r.store', $validated['stores']);
        }

        // Apply product type filter
        if ($productType === 'regular') {
            $query->where('ri.itemdepartment', '<>', 'NON PRODUCT')
                  ->orWhereNull('ri.itemdepartment');
        } elseif ($productType === 'non_product') {
            $query->where('ri.itemdepartment', '=', 'NON PRODUCT');
        }
        
        $products = $query
            ->groupBy('r.itemname', 'r.itemid', 'ri.itemdepartment')
            ->havingRaw('SUM(ABS(r.qty)) > 0 OR SUM(ABS(r.netamount)) > 0')
            ->get();

        \Log::info('Top/Bottom Products Query Result', [
            'product_type' => $productType,
            'total_products' => $products->count(),
            'date_range' => [$validated['start_date'], $validated['end_date']],
            'sample_products' => $products->take(3)->toArray()
        ]);

        // Convert to array
        $productsArray = $products->map(function($item) {
            return [
                'itemname' => $item->itemname,
                'total_quantity' => round($item->total_quantity, 2),
                'total_sales' => round($item->total_sales, 2),
                'store_count' => $item->store_count,
                'retail_department' => $item->retail_department,
                'product_category' => $item->product_category
            ];
        })->toArray();

        // Top 20
        $topProducts = collect($productsArray)
            ->sortByDesc('total_sales')
            ->take(20)
            ->values()
            ->all();

        // Bottom 20
        $bottomProducts = collect($productsArray)
            ->sortBy('total_sales')
            ->take(20)
            ->values()
            ->all();

        // Category breakdown
        $categoryBreakdown = collect($productsArray)->countBy('product_category');
        
        return response()->json([
            'topProducts' => $topProducts,
            'bottomProducts' => $bottomProducts,
            'summary' => [
                'total_products' => count($productsArray),
                'product_type_filter' => $productType,
                'category_breakdown' => [
                    'REGULAR PRODUCT' => $categoryBreakdown->get('REGULAR PRODUCT', 0),
                    'NON PRODUCT' => $categoryBreakdown->get('NON PRODUCT', 0),
                    'total' => count($productsArray)
                ],
                'date_range' => [
                    'start' => $validated['start_date'],
                    'end' => $validated['end_date']
                ]
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Top/Bottom Products Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'input' => $request->all()
        ]);
        return response()->json([
            'error' => 'Unable to retrieve products data',
            'message' => $e->getMessage(),
            'topProducts' => [],
            'bottomProducts' => [],
            'summary' => [
                'total_products' => 0,
                'product_type_filter' => $validated['product_type'] ?? 'all',
                'category_breakdown' => [
                    'REGULAR PRODUCT' => 0,
                    'NON PRODUCT' => 0,
                    'total' => 0
                ],
                'date_range' => [
                    'start' => $validated['start_date'] ?? null,
                    'end' => $validated['end_date'] ?? null
                ]
            ]
        ], 200);
    }
}


    public function getMonthlySales(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'exists:rbostoretables,NAME'
            ]);

            \Log::info('Monthly Sales Request Received', [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'stores' => $validated['stores'] ?? 'All Stores'
            ]);

            $query = rbotransactiontables::select(
                DB::raw('YEAR(createddate) as year'),
                DB::raw('MONTH(createddate) as month'),
                DB::raw('SUM(grossamount) as total_sales')
            )
            ->whereBetween('createddate', [
                $validated['start_date'],
                $validated['end_date']
            ]);

            if (!empty($validated['stores'])) {
                $query->whereIn('store', $validated['stores']);
            }

            $monthlySales = $query
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function($item) {
                    return [
                        'label' => date('F Y', mktime(0, 0, 0, $item->month, 1, $item->year)),
                        'total_sales' => $item->total_sales
                    ];
                });

            \Log::info('Monthly Sales Data', [
                'total_months' => $monthlySales->count()
            ]);

            return response()->json($monthlySales);
        } catch (\Exception $e) {
            \Log::error('Monthly Sales Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to retrieve monthly sales'], 500);
        }
    }

    public function getTopWastes(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'string'
            ]);

            // Base query using stockcountingtrans table for more accurate waste data
            $query = DB::table('stockcountingtrans as s')
                ->join('inventtables as i', 's.ITEMID', '=', 'i.ITEMID')
                ->leftJoin('inventtablemodules as m', 's.ITEMID', '=', 'm.ITEMID')
                ->select(
                    's.ITEMID',
                    'i.itemname',
                    's.WASTETYPE as waste_type',
                    DB::raw('SUM(ABS(s.WASTECOUNT)) as total_waste_quantity'),
                    DB::raw('SUM(ABS(s.WASTECOUNT * COALESCE(m.priceincltax, 0))) as total_waste_cost'),
                    DB::raw('COUNT(DISTINCT s.STORENAME) as store_count')
                )
                ->whereBetween('s.TRANSDATE', [
                    $validated['start_date'] . ' 00:00:00',
                    $validated['end_date'] . ' 23:59:59'
                ])
                ->where('s.WASTECOUNT', '>', 0)
                ->whereNotNull('s.WASTETYPE');

            if (!empty($validated['stores'])) {
                $query->whereIn('s.STORENAME', $validated['stores']);
            }

            // Get top waste items grouped by item and waste type
            $topWastesByType = $query
                ->groupBy('s.ITEMID', 'i.itemname', 's.WASTETYPE')
                ->orderByDesc('total_waste_quantity')
                ->limit(20)
                ->get();

            // Get overall top waste items (aggregated across all waste types)
            $topWastesOverall = DB::table('stockcountingtrans as s')
                ->join('inventtables as i', 's.ITEMID', '=', 'i.ITEMID')
                ->leftJoin('inventtablemodules as m', 's.ITEMID', '=', 'm.ITEMID')
                ->select(
                    's.ITEMID',
                    'i.itemname',
                    DB::raw('SUM(ABS(s.WASTECOUNT)) as total_waste_quantity'),
                    DB::raw('SUM(ABS(s.WASTECOUNT * COALESCE(m.priceincltax, 0))) as total_waste_cost'),
                    DB::raw('COUNT(DISTINCT s.STORENAME) as store_count'),
                    DB::raw('GROUP_CONCAT(DISTINCT s.WASTETYPE ORDER BY s.WASTETYPE) as waste_types')
                )
                ->whereBetween('s.TRANSDATE', [
                    $validated['start_date'] . ' 00:00:00',
                    $validated['end_date'] . ' 23:59:59'
                ])
                ->where('s.WASTECOUNT', '>', 0)
                ->whereNotNull('s.WASTETYPE');

            if (!empty($validated['stores'])) {
                $topWastesOverall->whereIn('s.STORENAME', $validated['stores']);
            }

            $topWastesOverall = $topWastesOverall
                ->groupBy('s.ITEMID', 'i.itemname')
                ->orderByDesc('total_waste_quantity')
                ->limit(10)
                ->get();

            // Get waste summary by type
            $wasteSummaryByType = DB::table('stockcountingtrans as s')
                ->select(
                    's.WASTETYPE as waste_type',
                    DB::raw('SUM(ABS(s.WASTECOUNT)) as total_quantity'),
                    DB::raw('COUNT(DISTINCT s.ITEMID) as unique_items'),
                    DB::raw('COUNT(DISTINCT s.STORENAME) as store_count')
                )
                ->whereBetween('s.TRANSDATE', [
                    $validated['start_date'] . ' 00:00:00',
                    $validated['end_date'] . ' 23:59:59'
                ])
                ->where('s.WASTECOUNT', '>', 0)
                ->whereNotNull('s.WASTETYPE');

            if (!empty($validated['stores'])) {
                $wasteSummaryByType->whereIn('s.STORENAME', $validated['stores']);
            }

            $wasteSummaryByType = $wasteSummaryByType
                ->groupBy('s.WASTETYPE')
                ->orderByDesc('total_quantity')
                ->get();

            return response()->json([
                'topWastesByType' => $topWastesByType,
                'topWastesOverall' => $topWastesOverall,
                'wasteSummaryByType' => $wasteSummaryByType,
                'summary' => [
                    'totalWasteCost' => $topWastesOverall->sum('total_waste_cost'),
                    'totalWasteQuantity' => $topWastesOverall->sum('total_waste_quantity'),
                    'uniqueStores' => $topWastesOverall->max('store_count'),
                    'dateRange' => [
                        'start' => $validated['start_date'],
                        'end' => $validated['end_date']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Top Wastes Error (Stock Counting)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to retrieve waste data from stock counting'], 500);
        }
    }

    private function getAnnouncements()
    {
        return announcements::latest()
            ->take(5)
            ->get()
            ->map(fn($announcement) => [
                'title' => $announcement->title,
                'description' => $announcement->description,
            ]);
    }

    public function admin() {
        $role = Auth::user()->role;

        $announcements = announcements::select('*')->get();

        if (in_array($role, ['SUPERADMIN', 'ADMIN', 'OPIC'])) {
            $metrics = $this->calculateMetrics();

            \Log::info('Metrics being passed:', $metrics);

            return Inertia::render('Home/admin', [
                'metrics' => $metrics,
                'announcements' => $this->getAnnouncements(),
                'username' => auth()->user()->name,
            ]);
        } else {
            return Inertia::render('Home/stores', ['announcements' => $announcements]);
        }
    }

    public function pos() {
        $windowtables = windowtables::all();
        $windowtrans = windowtrans::all();
    
        $windows = DB::table('windowtables as a')
            ->leftJoin('windowtrans as b', 'a.id', '=', 'b.windownum')
            ->select('a.*', 'b.*') 
            ->where('b.windownum', '=', '1')
            ->get();
    
        return Inertia::render('Home/pos', [
            'windowtables' => $windowtables,
            'windowtrans' => $windowtrans,
            'windows' => $windows,
        ]);
    }
    
    public function downloadFile($id)
    {
        $announcement = Announcements::findOrFail($id);
        
        if (!$announcement->file_path) {
            return redirect()->back()
                    ->with('message', 'File not found!')
                    ->with('isError', true);
        }

        $path = storage_path('app/public/' . $announcement->file_path);

        if (!file_exists($path)) {
            return redirect()->back()
                    ->with('message', 'File not exist!')
                    ->with('isError', true);
        }

        return response()->download($path);
    }

    public function getProducts(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            
            $query = DB::table('rbotransactionsalestrans as r')
                ->leftJoin('rboinventtables as ri', 'r.itemid', '=', 'ri.itemid')
                ->select(
                    'r.itemid',
                    'r.itemname',
                    DB::raw('COALESCE(ri.itemdepartment, "UNKNOWN") as itemdepartment'),
                    DB::raw('COALESCE(ri.category, "regular") as category')
                )
                ->groupBy('r.itemid', 'r.itemname', 'ri.itemdepartment', 'ri.category');

            if (!empty($searchTerm)) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('r.itemname', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('r.itemid', 'LIKE', '%' . $searchTerm . '%');
                });
            }

            $products = $query->get();

            return response()->json($products);
        } catch (\Exception $e) {
            \Log::error('Products fetch error', [
                'message' => $e->getMessage(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to fetch products'], 500);
        }
    }

    public function getCategories(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            
            $query = DB::table('rboinventtables')
                ->select('category')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->groupBy('category')
                ->orderBy('category');

            if (!empty($searchTerm)) {
                $query->where('category', 'LIKE', '%' . $searchTerm . '%');
            }

            $categories = $query->get()->map(function($item) {
                return [
                    'value' => $item->category,
                    'label' => $item->category
                ];
            });

            return response()->json($categories);
        } catch (\Exception $e) {
            \Log::error('Categories fetch error', [
                'message' => $e->getMessage(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to fetch categories'], 500);
        }
    }

    public function getTransactionSales(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'string',
                'filter_by' => 'sometimes|string|in:qty,gross',
                'products' => 'sometimes|array',
                'products.*' => 'string'
            ]);

            $filterBy = $validated['filter_by'] ?? 'gross';
            
            $query = DB::table('rbotransactionsalestrans as r')
                ->select(
                    DB::raw('DATE(r.createddate) as date'),
                    $filterBy === 'qty' 
                        ? DB::raw('SUM(ABS(r.qty)) as total_value')
                        : DB::raw('SUM(ABS(r.grossamount)) as total_value'),
                    DB::raw('COUNT(DISTINCT r.transactionid) as transaction_count'),
                    DB::raw('COUNT(DISTINCT r.store) as store_count')
                )
                ->whereDate('r.createddate', '>=', $validated['start_date'])
                ->whereDate('r.createddate', '<=', $validated['end_date']);

            if (!empty($validated['stores'])) {
                $query->whereIn('r.store', $validated['stores']);
            }

            if (!empty($validated['products'])) {
                $query->whereIn('r.itemid', $validated['products']);
            }

            $salesData = $query
                ->groupBy(DB::raw('DATE(r.createddate)'))
                ->orderBy('date')
                ->get()
                ->map(function($item) use ($filterBy) {
                    return [
                        'date' => $item->date,
                        'label' => date('M d, Y', strtotime($item->date)),
                        'total_value' => round($item->total_value, 2),
                        'transaction_count' => $item->transaction_count,
                        'store_count' => $item->store_count,
                        'metric_type' => $filterBy
                    ];
                });

            return response()->json([
                'data' => $salesData,
                'summary' => [
                    'total_days' => $salesData->count(),
                    'filter_by' => $filterBy,
                    'total_value' => $salesData->sum('total_value'),
                    'avg_daily_value' => $salesData->avg('total_value'),
                    'date_range' => [
                        'start' => $validated['start_date'],
                        'end' => $validated['end_date']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Transaction Sales Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to retrieve transaction sales data'], 500);
        }
    }

    public function getSalesByHour(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'string'
            ]);

            $query = DB::table('rbotransactionsalestrans as r')
                ->select(
                    DB::raw('HOUR(r.createddate) as hour'),
                    DB::raw('SUM(ABS(r.grossamount)) as total_sales'),
                    DB::raw('SUM(ABS(r.qty)) as total_quantity'),
                    DB::raw('COUNT(DISTINCT r.transactionid) as transaction_count'),
                    DB::raw('COUNT(DISTINCT r.store) as store_count')
                )
                ->whereDate('r.createddate', '>=', $validated['start_date'])
                ->whereDate('r.createddate', '<=', $validated['end_date']);

            if (!empty($validated['stores'])) {
                $query->whereIn('r.store', $validated['stores']);
            }

            $hourlyData = $query
                ->groupBy(DB::raw('HOUR(r.createddate)'))
                ->orderBy('hour')
                ->get()
                ->map(function($item) {
                    $hour = $item->hour;
                    $hourLabel = $hour == 0 ? '12 AM' : 
                                ($hour < 12 ? $hour . ' AM' : 
                                ($hour == 12 ? '12 PM' : ($hour - 12) . ' PM'));
                    
                    return [
                        'hour' => $hour,
                        'label' => $hourLabel,
                        'total_sales' => round($item->total_sales, 2),
                        'total_quantity' => round($item->total_quantity, 2),
                        'transaction_count' => $item->transaction_count,
                        'store_count' => $item->store_count
                    ];
                });

            // Fill missing hours with 0 values
            $allHours = collect(range(0, 23))->map(function($hour) use ($hourlyData) {
                $existing = $hourlyData->firstWhere('hour', $hour);
                if ($existing) {
                    return $existing;
                }
                
                $hourLabel = $hour == 0 ? '12 AM' : 
                            ($hour < 12 ? $hour . ' AM' : 
                            ($hour == 12 ? '12 PM' : ($hour - 12) . ' PM'));
                
                return [
                    'hour' => $hour,
                    'label' => $hourLabel,
                    'total_sales' => 0,
                    'total_quantity' => 0,
                    'transaction_count' => 0,
                    'store_count' => 0
                ];
            });

            return response()->json([
                'data' => $allHours,
                'summary' => [
                    'peak_hour' => $hourlyData->sortByDesc('total_sales')->first(),
                    'total_sales' => $hourlyData->sum('total_sales'),
                    'avg_hourly_sales' => $hourlyData->avg('total_sales'),
                    'date_range' => [
                        'start' => $validated['start_date'],
                        'end' => $validated['end_date']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Sales by Hour Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to retrieve hourly sales data'], 500);
        }
    }

    public function getTopStores(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'string',
                'filter_by' => 'sometimes|string|in:grossamount,discamount,netamount,qty'
            ]);

            $filterBy = $validated['filter_by'] ?? 'grossamount';
            
            $selectField = match($filterBy) {
                'grossamount' => 'r.grossamount',
                'discamount' => 'r.discamount', 
                'netamount' => 'r.netamount',
                'qty' => 'r.qty',
                default => 'r.grossamount'
            };

            $query = DB::table('rbotransactionsalestrans as r')
                ->select(
                    'r.store',
                    DB::raw("SUM(ABS({$selectField})) as total_value"),
                    DB::raw('COUNT(DISTINCT r.transactionid) as transaction_count'),
                    DB::raw('COUNT(DISTINCT r.itemid) as unique_items'),
                    DB::raw('SUM(ABS(r.qty)) as total_quantity')
                )
                ->whereDate('r.createddate', '>=', $validated['start_date'])
                ->whereDate('r.createddate', '<=', $validated['end_date']);

            if (!empty($validated['stores'])) {
                $query->whereIn('r.store', $validated['stores']);
            }

            $storeData = $query
                ->groupBy('r.store')
                ->orderByDesc('total_value')
                ->limit(20)
                ->get()
                ->map(function($item) use ($filterBy) {
                    return [
                        'store' => $item->store,
                        'total_value' => round($item->total_value, 2),
                        'transaction_count' => $item->transaction_count,
                        'unique_items' => $item->unique_items,
                        'total_quantity' => round($item->total_quantity, 2),
                        'metric_type' => $filterBy
                    ];
                });

            return response()->json([
                'data' => $storeData,
                'summary' => [
                    'total_stores' => $storeData->count(),
                    'filter_by' => $filterBy,
                    'top_store' => $storeData->first(),
                    'total_value' => $storeData->sum('total_value'),
                    'avg_store_value' => $storeData->avg('total_value'),
                    'date_range' => [
                        'start' => $validated['start_date'],
                        'end' => $validated['end_date']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Top Stores Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to retrieve top stores data'], 500);
        }
    }

    public function getAdvancedAnalysis(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'string'
            ]);

            // Sales Trend Analysis
            $salesTrend = DB::table('rbotransactionsalestrans as r')
                ->select(
                    DB::raw('DATE(r.createddate) as date'),
                    DB::raw('SUM(ABS(r.grossamount)) as gross_sales'),
                    DB::raw('SUM(ABS(r.netamount)) as net_sales'),
                    DB::raw('SUM(ABS(r.discamount)) as discount_amount'),
                    DB::raw('COUNT(DISTINCT r.transactionid) as transactions')
                )
                ->whereDate('r.createddate', '>=', $validated['start_date'])
                ->whereDate('r.createddate', '<=', $validated['end_date']);

            if (!empty($validated['stores'])) {
                $salesTrend->whereIn('r.store', $validated['stores']);
            }

            $salesTrendData = $salesTrend
                ->groupBy(DB::raw('DATE(r.createddate)'))
                ->orderBy('date')
                ->get();

            // Store Performance Comparison
            $storeComparison = DB::table('rbotransactionsalestrans as r')
                ->select(
                    'r.store',
                    DB::raw('SUM(ABS(r.grossamount)) as total_sales'),
                    DB::raw('AVG(ABS(r.grossamount)) as avg_transaction'),
                    DB::raw('COUNT(DISTINCT r.transactionid) as transaction_count'),
                    DB::raw('SUM(ABS(r.qty)) as total_items_sold')
                )
                ->whereDate('r.createddate', '>=', $validated['start_date'])
                ->whereDate('r.createddate', '<=', $validated['end_date']);

            if (!empty($validated['stores'])) {
                $storeComparison->whereIn('r.store', $validated['stores']);
            }

            $storeComparisonData = $storeComparison
                ->groupBy('r.store')
                ->orderByDesc('total_sales')
                ->get();

            // Category Performance (simplified since we don't have retail department)
            $categoryQuery = DB::table('rbotransactionsalestrans as r')
                ->select(
                    'r.itemname',
                    DB::raw('SUM(ABS(r.grossamount)) as item_total_sales'),
                    DB::raw('SUM(ABS(r.qty)) as item_total_quantity'),
                    'r.itemid'
                )
                ->whereDate('r.createddate', '>=', $validated['start_date'])
                ->whereDate('r.createddate', '<=', $validated['end_date']);

            if (!empty($validated['stores'])) {
                $categoryQuery->whereIn('r.store', $validated['stores']);
            }

            $itemData = $categoryQuery
                ->groupBy('r.itemname', 'r.itemid')
                ->get();

            // Process category data in PHP to avoid MySQL GROUP BY issues
            $categoryTotals = [
                'Bakery' => ['total_sales' => 0, 'total_quantity' => 0, 'unique_items' => 0],
                'Beverages' => ['total_sales' => 0, 'total_quantity' => 0, 'unique_items' => 0],
                'Services' => ['total_sales' => 0, 'total_quantity' => 0, 'unique_items' => 0],
                'Other Products' => ['total_sales' => 0, 'total_quantity' => 0, 'unique_items' => 0]
            ];

            foreach ($itemData as $item) {
                $category = 'Other Products';
                $itemName = strtoupper($item->itemname ?? '');
                
                if (str_contains($itemName, 'CAKE') || str_contains($itemName, 'BREAD')) {
                    $category = 'Bakery';
                } elseif (str_contains($itemName, 'COFFEE') || str_contains($itemName, 'TEA')) {
                    $category = 'Beverages';
                } elseif (str_contains($itemName, 'SERVICE') || str_contains($itemName, 'FEE')) {
                    $category = 'Services';
                }
                
                $categoryTotals[$category]['total_sales'] += $item->item_total_sales;
                $categoryTotals[$category]['total_quantity'] += $item->item_total_quantity;
                $categoryTotals[$category]['unique_items']++;
            }

            // Convert to collection format expected by the response
            $categoryData = collect($categoryTotals)->map(function($data, $category) {
                return (object) [
                    'category' => $category,
                    'total_sales' => $data['total_sales'],
                    'total_quantity' => $data['total_quantity'],
                    'unique_items' => $data['unique_items']
                ];
            })->sortByDesc('total_sales')->values();

            return response()->json([
                'salesTrend' => $salesTrendData->map(function($item) {
                    return [
                        'date' => $item->date,
                        'label' => date('M d', strtotime($item->date)),
                        'gross_sales' => round($item->gross_sales, 2),
                        'net_sales' => round($item->net_sales, 2),
                        'discount_amount' => round($item->discount_amount, 2),
                        'transactions' => $item->transactions
                    ];
                }),
                'storeComparison' => $storeComparisonData->map(function($item) {
                    return [
                        'store' => $item->store,
                        'total_sales' => round($item->total_sales, 2),
                        'avg_transaction' => round($item->avg_transaction, 2),
                        'transaction_count' => $item->transaction_count,
                        'total_items_sold' => round($item->total_items_sold, 2)
                    ];
                }),
                'categoryPerformance' => $categoryData->map(function($item) {
                    return [
                        'category' => $item->category,
                        'total_sales' => round($item->total_sales, 2),
                        'total_quantity' => round($item->total_quantity, 2),
                        'unique_items' => $item->unique_items
                    ];
                }),
                'summary' => [
                    'total_days_analyzed' => $salesTrendData->count(),
                    'total_stores_analyzed' => $storeComparisonData->count(),
                    'total_categories' => $categoryData->count(),
                    'date_range' => [
                        'start' => $validated['start_date'],
                        'end' => $validated['end_date']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Advanced Analysis Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to retrieve advanced analysis data'], 500);
        }
    }

    public function getReceivedDeliveryVsSales(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'string'
            ]);

            $query = DB::table('inventory_summaries as i')
                ->select(
                    DB::raw('DATE(i.report_date) as date'),
                    DB::raw('SUM(i.received_delivery) as total_received_delivery'),
                    DB::raw('SUM(i.sales + i.bundle_sales) as total_sales'),
                    DB::raw('COUNT(DISTINCT i.storename) as store_count')
                )
                ->whereDate('i.report_date', '>=', $validated['start_date'])
                ->whereDate('i.report_date', '<=', $validated['end_date']);

            if (!empty($validated['stores'])) {
                $query->whereIn('i.storename', $validated['stores']);
            }

            $data = $query
                ->groupBy(DB::raw('DATE(i.report_date)'))
                ->orderBy('date')
                ->get()
                ->map(function($item) {
                    return [
                        'date' => $item->date,
                        'label' => date('M d, Y', strtotime($item->date)),
                        'received_delivery' => round($item->total_received_delivery, 2),
                        'total_sales' => round($item->total_sales, 2),
                        'store_count' => $item->store_count,
                        'efficiency' => $item->total_received_delivery > 0 ? 
                            round(($item->total_sales / $item->total_received_delivery) * 100, 2) : 0
                    ];
                });

            return response()->json([
                'data' => $data,
                'summary' => [
                    'total_days' => $data->count(),
                    'total_received_delivery' => $data->sum('received_delivery'),
                    'total_sales' => $data->sum('total_sales'),
                    'overall_efficiency' => $data->sum('received_delivery') > 0 ? 
                        round(($data->sum('total_sales') / $data->sum('received_delivery')) * 100, 2) : 0,
                    'date_range' => [
                        'start' => $validated['start_date'],
                        'end' => $validated['end_date']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Received Delivery vs Sales Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to retrieve delivery vs sales data'], 500);
        }
    }

    public function getSalesByCategory(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'string',
                'categories' => 'sometimes|array',
                'categories.*' => 'string'
            ]);

            $query = DB::table('rbotransactionsalestrans as r')
                ->leftJoin('rboinventtables as ri', 'r.itemid', '=', 'ri.itemid')
                ->select(
                    'ri.category',
                    DB::raw('SUM(ABS(r.qty)) as total_quantity'),
                    DB::raw('SUM(ABS(r.netamount)) as total_sales'),
                    DB::raw('COUNT(DISTINCT r.itemid) as unique_items'),
                    DB::raw('COUNT(DISTINCT r.store) as store_count')
                )
                ->whereDate('r.createddate', '>=', $validated['start_date'])
                ->whereDate('r.createddate', '<=', $validated['end_date'])
                ->where(function($q) {
                    $q->where('r.qty', '>', 0)
                      ->orWhere('r.netamount', '>', 0);
                });

            if (!empty($validated['stores'])) {
                $query->whereIn('r.store', $validated['stores']);
            }

            if (!empty($validated['categories'])) {
                $query->whereIn('ri.category', $validated['categories']);
            }

            $categoryData = $query
                ->groupBy('ri.category')
                ->orderByDesc('total_sales')
                ->havingRaw('SUM(ABS(r.netamount)) > 0')
                ->get()
                ->map(function($item) {
                    return [
                        'category' => $item->category ?: 'UNCATEGORIZED',
                        'total_quantity' => round($item->total_quantity, 2),
                        'total_sales' => round($item->total_sales, 2),
                        'unique_items' => $item->unique_items,
                        'store_count' => $item->store_count
                    ];
                });

            \Log::info('Sales by Category Query Result', [
                'total_categories' => $categoryData->count(),
                'sample_data' => $categoryData->take(5)->toArray(),
                'date_range' => [$validated['start_date'], $validated['end_date']]
            ]);

            return response()->json([
                'data' => $categoryData,
                'summary' => [
                    'total_categories' => $categoryData->count(),
                    'total_sales' => $categoryData->sum('total_sales'),
                    'total_quantity' => $categoryData->sum('total_quantity'),
                    'date_range' => [
                        'start' => $validated['start_date'],
                        'end' => $validated['end_date']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Sales by Category Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json([
                'error' => 'Unable to retrieve sales by category data',
                'message' => $e->getMessage(),
                'data' => [],
                'summary' => [
                    'total_categories' => 0,
                    'total_sales' => 0,
                    'total_quantity' => 0,
                    'date_range' => [
                        'start' => $validated['start_date'] ?? null,
                        'end' => $validated['end_date'] ?? null
                    ]
                ]
            ], 200);
        }
    }

    public function getTopVarianceStores(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'stores' => 'sometimes|array',
                'stores.*' => 'string'
            ]);

            $query = DB::table('inventory_summaries as i')
                ->select(
                    'i.storename',
                    DB::raw('SUM(ABS(i.variance)) as total_variance'),
                    DB::raw('COUNT(*) as variance_count'),
                    DB::raw('AVG(ABS(i.variance)) as avg_variance'),
                    DB::raw('SUM(i.sales + i.bundle_sales) as total_sales')
                )
                ->whereDate('i.report_date', '>=', $validated['start_date'])
                ->whereDate('i.report_date', '<=', $validated['end_date'])
                ->where('i.variance', '!=', 0);

            if (!empty($validated['stores'])) {
                $query->whereIn('i.storename', $validated['stores']);
            }

            $storeVariances = $query
                ->groupBy('i.storename')
                ->orderByDesc('total_variance')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'store' => $item->storename,
                        'total_variance' => round($item->total_variance, 2),
                        'variance_count' => $item->variance_count,
                        'avg_variance' => round($item->avg_variance, 2),
                        'total_sales' => round($item->total_sales, 2),
                        'variance_to_sales_ratio' => $item->total_sales > 0 ? 
                            round(($item->total_variance / $item->total_sales) * 100, 2) : 0
                    ];
                });

            return response()->json([
                'data' => $storeVariances,
                'summary' => [
                    'total_stores_with_variance' => $storeVariances->count(),
                    'total_variance' => $storeVariances->sum('total_variance'),
                    'avg_variance_per_store' => $storeVariances->avg('avg_variance'),
                    'date_range' => [
                        'start' => $validated['start_date'],
                        'end' => $validated['end_date']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Top Variance Stores Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Unable to retrieve variance data'], 500);
        }
    }

    public function offline(){
        return Inertia::render('Offline');
    }
}