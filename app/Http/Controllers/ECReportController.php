<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\rbotransactiontables;
use App\Models\rbotransactionsalestrans;
use App\Models\stockcountingtrans;
use App\Models\receivedordertrans;
use App\Models\wastedeclarationtrans;
use App\Models\inventtables;
use App\Models\rbostoretables;
use Inertia\Inertia;
use Carbon\Carbon;
use ApacheSpark\SparkContext;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\InventorySummary;


use Illuminate\Http\Request;

class ECReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = Auth::user()->role;
        return Inertia::render('Reports/index',['userRole' => $role,]);
    }

    public function ar(Request $request)
{
    // Validate request
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    // Build query
    $query = rbotransactiontables::select(
        'rbotransactiontables.receiptid',
        'rbotransactiontables.store',
        'rbostoretables.NAME as storename',
        DB::raw('CAST(createddate as date) as createddate'),  
        DB::raw('CAST(IFNULL(charge, 0) AS DECIMAL(10,0)) as charge'),  
        DB::raw('CAST(IFNULL(gcash, 0) AS DECIMAL(10,0)) as gcash'),  
        DB::raw('CAST(IFNULL(paymaya, 0) AS DECIMAL(10,0)) as paymaya'),  
        DB::raw('CAST(IFNULL(card, 0) AS DECIMAL(10,0)) as card'),  
        DB::raw('CAST(IFNULL(loyaltycard, 0) AS DECIMAL(10,0)) as loyaltycard'), 
        DB::raw('CAST(IFNULL(foodpanda, 0) AS DECIMAL(10,0)) as foodpanda'),  
        DB::raw('CAST(IFNULL(grabfood, 0) AS DECIMAL(10,0)) as grabfood'),  
        DB::raw('CAST(IFNULL(representation, 0) AS DECIMAL(10,0)) as representation'),
        DB::raw('CAST((
            IFNULL(charge, 0) + IFNULL(gcash, 0) + IFNULL(paymaya, 0) + 
            IFNULL(card, 0) + IFNULL(loyaltycard, 0) + IFNULL(foodpanda, 0) + 
            IFNULL(grabfood, 0) + IFNULL(representation, 0)
        ) AS DECIMAL(10,0)) as total_amount')
    )
    ->join('rbostoretables', 'rbotransactiontables.store', '=', 'rbostoretables.NAME');

    // Modified where clause to find transactions with receivables
    $query->where(function($q) {
        $q->where('charge', '>', 0)
        ->orWhere('gcash', '>', 0)
        ->orWhere('paymaya', '>', 0)
        ->orWhere('card', '>', 0)
        ->orWhere('loyaltycard', '>', 0)
        ->orWhere('foodpanda', '>', 0)
        ->orWhere('grabfood', '>', 0)
        ->orWhere('representation', '>', 0);
    });

    // Apply date filters if provided
    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            Carbon::parse($request->startDate)->startOfDay(),
            Carbon::parse($request->endDate)->endOfDay()
        ]);
    }

    // Apply store filters based on user role
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactiontables.store', $userStoreId);
    }

    // Get available stores based on user role
    $stores = rbostoretables::select('STOREID', 'NAME')
        ->when($role !== 'ADMIN' && $role !== 'SUPERADMIN', function($query) use ($userStoreId) {
            $query->where('STOREID', $userStoreId);
        })
        ->orderBy('NAME')
        ->get();

    // Get AR records
    $ar = $query->orderBy('createddate', 'desc')->get();

    // Calculate totals - Use integer values for totals
    $totals = [
        'charge' => (int)$ar->sum('charge'),
        'gcash' => (int)$ar->sum('gcash'),
        'paymaya' => (int)$ar->sum('paymaya'),
        'card' => (int)$ar->sum('card'),
        'loyaltycard' => (int)$ar->sum('loyaltycard'),
        'foodpanda' => (int)$ar->sum('foodpanda'),
        'grabfood' => (int)$ar->sum('grabfood'),
        'representation' => (int)$ar->sum('representation'),
        'total' => (int)$ar->sum('total_amount')
    ];

    return Inertia::render('Reports/AccountReceivable', [
        'ar' => $ar,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? []
        ]
    ]);
}

public function ec(Request $request)
{
    // Validate request
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;
    $isLoading = false;
    
    // Build query
    $query = rbotransactiontables::select(
        'rbotransactiontables.receiptid',
        'rbotransactiontables.store',
        'rbostoretables.NAME as storename',
        'rbotransactiontables.custaccount as custaccount',
        DB::raw('CAST(createddate as date) as createddate'),  
        DB::raw('CAST(IFNULL(grossamount, 0) AS DECIMAL(10,2)) as grossamount'),
        DB::raw('CAST(IFNULL(discamount, 0) AS DECIMAL(10,2)) as discamount'),
        DB::raw('CAST(IFNULL(netamount, 0) AS DECIMAL(10,2)) as netamount'),
        DB::raw('CAST(IFNULL(taxinclinprice, 0) AS DECIMAL(10,2)) as Vat'),
        DB::raw('CAST(IFNULL(netamountnotincltax, 0) AS DECIMAL(10,2)) as Vatablesales')
    )
    ->where('charge','>=', 1)
    ->join('rbostoretables', 'rbotransactiontables.store', '=', 'rbostoretables.NAME');

    // Apply date filters if provided
    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            Carbon::parse($request->startDate)->startOfDay(),
            Carbon::parse($request->endDate)->endOfDay()
        ]);
    }

    // Apply store filters based on user role
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactiontables.store', $userStoreId);
    }

    // Get available stores based on user role
    $stores = rbostoretables::select('STOREID', 'NAME')
        ->when($role !== 'ADMIN' && $role !== 'SUPERADMIN', function($query) use ($userStoreId) {
            $query->where('STOREID', $userStoreId);
        })
        ->orderBy('NAME')
        ->get();

    // Get EC records
    $ec = $query->orderBy('createddate', 'desc')->get();

    // Calculate totals
    $totals = [
        'grossamount' => (float)$ec->sum('grossamount'),
        'discamount' => (float)$ec->sum('discamount'),
        'netamount' => (float)$ec->sum('netamount'),
        'Vat' => (float)$ec->sum('Vat'),
        'Vatablesales' => (float)$ec->sum('Vatablesales')
    ];

    return Inertia::render('Reports/EmployeeCharge', [
        'ec' => $ec,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? []
        ]
    ]);
}

public function bo(Request $request)
{
    // Validate request
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;
    
    // Build query using stockcountingtrans table
    $query = stockcountingtrans::select(
        'stockcountingtrans.ITEMID as itemid',
        'rbostoretables.NAME as storename',
        'inventtables.itemname as itemname',
        'rboinventtables.itemgroup as category',
        'stockcountingtrans.TRANSDATE as batchdate',
        'stockcountingtrans.WASTEDATE as waste_declaration_date',
        'inventtablemodules.priceincltax as price',
        DB::raw("CAST(IFNULL(SUM(CASE WHEN UPPER(TRIM(stockcountingtrans.WASTETYPE)) IN ('THROW_AWAY', 'THROW AWAY') THEN stockcountingtrans.WASTECOUNT ELSE 0 END), 0) AS DECIMAL(10,2)) as throw_away"),
        DB::raw("CAST(IFNULL(SUM(CASE WHEN UPPER(TRIM(stockcountingtrans.WASTETYPE)) IN ('EARLY_MOLDS', 'EARLY MOLDS') THEN stockcountingtrans.WASTECOUNT ELSE 0 END), 0) AS DECIMAL(10,2)) as early_molds"),
        DB::raw("CAST(IFNULL(SUM(CASE WHEN UPPER(TRIM(stockcountingtrans.WASTETYPE)) IN ('PULL_OUT', 'PULL OUT') THEN stockcountingtrans.WASTECOUNT ELSE 0 END), 0) AS DECIMAL(10,2)) as pull_out"),
        DB::raw("CAST(IFNULL(SUM(CASE WHEN UPPER(TRIM(stockcountingtrans.WASTETYPE)) IN ('RAT_BITES', 'RAT BITES') THEN stockcountingtrans.WASTECOUNT ELSE 0 END), 0) AS DECIMAL(10,2)) as rat_bites"),
        DB::raw("CAST(IFNULL(SUM(CASE WHEN UPPER(TRIM(stockcountingtrans.WASTETYPE)) IN ('ANT_BITES', 'ANT BITES') THEN stockcountingtrans.WASTECOUNT ELSE 0 END), 0) AS DECIMAL(10,2)) as ant_bites")
    )
    ->join('rbostoretables', 'stockcountingtrans.STORENAME', '=', 'rbostoretables.NAME')
    ->join('inventtables', 'stockcountingtrans.ITEMID', '=', 'inventtables.itemid')
    ->join('rboinventtables', 'stockcountingtrans.ITEMID', '=', 'rboinventtables.itemid')
    ->leftJoin('inventtablemodules', 'stockcountingtrans.ITEMID', '=', 'inventtablemodules.itemid')
    ->where('stockcountingtrans.WASTECOUNT', '>', 0);

    // FIXED: Apply date filters consistently
    if ($request->filled('startDate') && $request->filled('endDate')) {
        // Both dates provided - use date range
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();
        
        $query->whereBetween('stockcountingtrans.TRANSDATE', [$startDate, $endDate]);
        
        Log::info('Date range filter applied', [
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s')
        ]);
    } elseif ($request->filled('startDate')) {
        // Only start date provided
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $query->where('stockcountingtrans.TRANSDATE', '>=', $startDate);
        
        Log::info('Start date filter applied', [
            'startDate' => $startDate->format('Y-m-d H:i:s')
        ]);
    } elseif ($request->filled('endDate')) {
        // Only end date provided
        $endDate = Carbon::parse($request->endDate)->endOfDay();
        $query->where('stockcountingtrans.TRANSDATE', '<=', $endDate);
        
        Log::info('End date filter applied', [
            'endDate' => $endDate->format('Y-m-d H:i:s')
        ]);
    }
    // FIXED: Remove default current date filter when no dates provided
    // This allows users to see all data when no date filters are applied

    // Apply store filters based on user role
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores') && !empty($request->stores)) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('stockcountingtrans.STORENAME', $userStoreId);
    }

    // Group and order results
    $query->groupBy(
        'stockcountingtrans.ITEMID',
        'rbostoretables.NAME',
        'inventtables.itemname',
        'rboinventtables.itemgroup',
        'stockcountingtrans.TRANSDATE',
        'stockcountingtrans.WASTEDATE',
        'inventtablemodules.priceincltax'
    );

    // Get the data
    $data = $query->orderBy('storename')->get()->map(function ($item) {
        // Calculate total waste count
        $totalWasteCount = (float) $item->throw_away + 
                         (float) $item->early_molds + 
                         (float) $item->pull_out + 
                         (float) $item->rat_bites + 
                         (float) $item->ant_bites;
        
        // Calculate total price (price * total waste)
        $totalPrice = (float) $item->price * $totalWasteCount;
        
        return [
            'itemid' => $item->itemid,
            'itemname' => $item->itemname,
            'category' => $item->category,
            'storename' => $item->storename,
            'batchdate' => $item->batchdate,
            'waste_declaration_date' => $item->waste_declaration_date,
            'price' => (float) $item->price,
            'throw_away' => (float) $item->throw_away,
            'early_molds' => (float) $item->early_molds,
            'pull_out' => (float) $item->pull_out,
            'rat_bites' => (float) $item->rat_bites,
            'ant_bites' => (float) $item->ant_bites,
            'total_price' => $totalPrice
        ];
    });

    // Get available stores for filtering
    $stores = [];
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->orderBy('NAME')
            ->get();
    } else {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->where('STOREID', $userStoreId)
            ->get();
    }

    // FIXED: Pass the correct prop name 'bo' instead of 'ar'
    return Inertia::render('Reports/BadOrders', [
        'bo' => $data,
        'stores' => $stores,
        'userRole' => $role,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? []
        ]
    ]);
}

public function ro(Request $request)
{
    // Validate request
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;
    
    // Build query using stockcountingtrans table with receivedcount field
    $query = stockcountingtrans::select(
        'stockcountingtrans.ITEMID as itemid',
        'rbostoretables.NAME as storename',
        'inventtables.itemname as itemname',
        'rboinventtables.itemgroup as category',
        'stockcountingtrans.TRANSDATE as received_date',
        // Add price including tax from inventtablemodules table
        'inventtablemodules.priceincltax as price',
        DB::raw("CAST(IFNULL(SUM(stockcountingtrans.RECEIVEDCOUNT), 0) AS DECIMAL(10,2)) as received_qty")
    )
    ->join('rbostoretables', 'stockcountingtrans.STORENAME', '=', 'rbostoretables.NAME')
    ->join('inventtables', 'stockcountingtrans.ITEMID', '=', 'inventtables.itemid')
    ->join('rboinventtables', 'stockcountingtrans.ITEMID', '=', 'rboinventtables.itemid')
    // Join with inventtablemodules to get price information
    ->leftJoin('inventtablemodules', 'stockcountingtrans.ITEMID', '=', 'inventtablemodules.itemid')
    ->where('stockcountingtrans.RECEIVEDCOUNT', '>', 0);

    // Apply date filters
    if ($request->filled(['startDate', 'endDate'])) {
        // Format dates properly for database comparison when both start and end dates are provided
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();
        
        // Use whereDate instead of whereBetween for more reliable date comparison
        $query->where(function($q) use ($startDate, $endDate) {
            $q->whereDate('stockcountingtrans.TRANSDATE', '>=', $startDate->format('Y-m-d'))
              ->whereDate('stockcountingtrans.TRANSDATE', '<=', $endDate->format('Y-m-d'));
        });
        
        // Add debug logging
        Log::info('Date filter applied on TRANSDATE with provided dates', [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
    } else {
        // If no date range is provided, default to current date
        $currentDate = Carbon::now()->format('Y-m-d');
        $query->whereDate('stockcountingtrans.TRANSDATE', $currentDate);
        
        // Add debug logging
        Log::info('Date filter applied on TRANSDATE with current date', [
            'currentDate' => $currentDate,
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
    }

    // Apply store filters based on user role
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('stockcountingtrans.STORENAME', $userStoreId);
    }

    // Group and order results
    $query->groupBy(
        'stockcountingtrans.ITEMID',
        'rbostoretables.NAME',
        'inventtables.itemname',
        'rboinventtables.itemgroup',
        'stockcountingtrans.TRANSDATE',
        'inventtablemodules.priceincltax'
    );

    // Format results
    $data = $query->orderBy('storename')->get()->map(function ($item) {
        // Calculate total price (price * received quantity)
        $totalPrice = (float) $item->price * (float) $item->received_qty;
        
        return [
            'itemid' => $item->itemid,
            'itemname' => $item->itemname,
            'category' => $item->category,
            'storename' => $item->storename,
            'received_date' => $item->received_date,
            'price' => (float) $item->price,
            'received_qty' => (float) $item->received_qty,
            'total_price' => $totalPrice // Add calculated total price
        ];
    });

    // Get available stores for filtering
    $stores = [];
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->orderBy('NAME')
            ->get();
    } else {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->where('STOREID', $userStoreId)
            ->get();
    }

    // Return the view with data
    return Inertia::render('Reports/ReceivedOrders', [
        'ro' => $data,
        'stores' => $stores,
        'userRole' => $role,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? []
        ]
    ]);
}

public function rd(Request $request)
{
    // Validate request
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;
    $isLoading = false;
    
    $query = rbotransactionsalestrans::select(
        'rbotransactionsalestrans.receiptid',
        'rbotransactionsalestrans.itemname',
        'rbotransactionsalestrans.store',
        'rbostoretables.NAME as storename',
        DB::raw('CAST(createddate as date) as createddate'),
        DB::raw('CASE 
            WHEN rbotransactionsalestrans.discofferid LIKE "%SENIOR%" 
            THEN rbotransactionsalestrans.discamount 
            ELSE 0 
        END as senior_discount'),
        DB::raw('CASE 
            WHEN rbotransactionsalestrans.discofferid LIKE "%PWD%" 
            THEN rbotransactionsalestrans.discamount 
            ELSE 0 
        END as pwd_discount'),
        DB::raw('CASE 
            WHEN rbotransactionsalestrans.discofferid = "25% One Day Before"
            THEN rbotransactionsalestrans.discamount 
            ELSE 0 
        END as one_day_before_discount'),
        'rbotransactionsalestrans.grossamount as grossamount',
        'rbotransactionsalestrans.discofferid as discname'
    )
    ->join('rbostoretables', 'rbotransactionsalestrans.store', '=', 'rbostoretables.NAME')
    ->where('rbotransactionsalestrans.discamount', '>', 0)
    ->where(function($query) {
        $query->where('rbotransactionsalestrans.discofferid', 'LIKE', '%SENIOR%')
              ->orWhere('rbotransactionsalestrans.discofferid', 'LIKE', '%PWD%')
              ->orWhere('rbotransactionsalestrans.discofferid', '=', '25% One Day Before');
    });

    // Apply date filters if provided
    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            Carbon::parse($request->startDate)->startOfDay(),
            Carbon::parse($request->endDate)->endOfDay()
        ]);
    }

    // Apply store filters based on user role
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactionsalestrans.store', $userStoreId);
    }

    // Get available stores based on user role
    $stores = [];
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->orderBy('NAME')
            ->get();
    } else {
        $stores = rbostoretables::select('STOREID', 'NAME')
            ->where('STOREID', $userStoreId)
            ->get();
    }

    // Get data
    $rd = $query->orderBy('createddate', 'desc')->get();

    // Calculate totals
    $totals = [
        'senior_discount' => $rd->sum('senior_discount'),
        'pwd_discount' => $rd->sum('pwd_discount'),
        'one_day_before_discount' => $rd->sum('one_day_before_discount')
    ];

    return Inertia::render('Reports/RegularDiscount', [
        'rd' => $rd,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? []
        ]
    ]);
}


public function inventory(Request $request)
{
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    $startDate = $request->filled('startDate') ? 
        Carbon::parse($request->startDate)->format('Y-m-d') : 
        Carbon::yesterday()->timezone('Asia/Manila')->format('Y-m-d');

    $endDate = $request->filled('endDate') ? 
        Carbon::parse($request->endDate)->format('Y-m-d') : 
        Carbon::today()->timezone('Asia/Manila')->format('Y-m-d');

    try {
        // Get stores based on user role
        $stores = ($role === 'ADMIN' || $role === 'SUPERADMIN') 
            ? rbostoretables::select('STOREID', 'NAME')->orderBy('NAME')->get()
            : rbostoretables::select('STOREID', 'NAME')->where('STOREID', $userStoreId)->get();

        // Build the base query - Get individual records instead of aggregating
        $query = DB::table('inventory_summaries')
            ->whereBetween('report_date', [$startDate, $endDate]);

        // Apply store filters based on user role
        if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
            if ($request->filled('stores')) {
                $query->whereIn('storename', $request->stores);
            }
        } else {
            $query->where('storename', $userStoreId);
        }

        // Get individual records with all fields including id
        $inventoryData = $query
            ->select([
                'id',
                'itemid',
                'itemname',
                'storename',
                'beginning',
                'received_delivery',
                'stock_transfer',
                'sales',
                'bundle_sales',
                'throw_away',
                'early_molds',
                'pull_out',
                'rat_bites',
                'ant_bites',
                'item_count',
                'ending',
                'variance',
                'report_date'
            ])
            ->whereNotLike('itemid', '%PRM-PRO%')
            ->orderBy('itemname')
            ->orderBy('storename')
            ->orderBy('report_date')
            ->get()
            ->map(function ($item) {
                // Ensure all numeric values are properly formatted as floats
                return [
                    'id' => $item->id,
                    'itemid' => $item->itemid,
                    'itemname' => $item->itemname,
                    'storename' => $item->storename,
                    'beginning' => (float) ($item->beginning ?? 0),
                    'received_delivery' => (float) ($item->received_delivery ?? 0),
                    'stock_transfer' => (float) ($item->stock_transfer ?? 0),
                    'sales' => (float) ($item->sales ?? 0),
                    'bundle_sales' => (float) ($item->bundle_sales ?? 0),
                    'throw_away' => (float) ($item->throw_away ?? 0),
                    'early_molds' => (float) ($item->early_molds ?? 0),
                    'pull_out' => (float) ($item->pull_out ?? 0),
                    'rat_bites' => (float) ($item->rat_bites ?? 0),
                    'ant_bites' => (float) ($item->ant_bites ?? 0),
                    'item_count' => (float) ($item->item_count ?? 0),
                    'ending' => (float) ($item->ending ?? 0),
                    'variance' => (float) ($item->variance ?? 0),
                    'report_date' => $item->report_date
                ];
            });

        return Inertia::render('Reports/Inventory', [
            'inventory' => $inventoryData,
            'stores' => $stores,
            'userRole' => $role,
            'filters' => [
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'selectedStores' => $request->stores ?? []
            ],
            'url' => route('reports.inventory')
        ]);

    } catch (\Exception $e) {
        Log::error('Inventory Report Error:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()->with('error', 'An error occurred while generating the inventory report: ' . $e->getMessage());
    }
}

    /**
 * Get bundle sales for item
 */
private function getBundleSalesForItem($itemId, $storeName, $startDate, $endDate)
{
    try {
        // Check if table exists before querying
        if (Schema::hasTable('bundle_sales')) {
            return DB::table('bundle_sales')
                ->where('component_itemid', $itemId)
                ->where('store', $storeName)
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->sum('qty') ?? 0;
        }
        
        // If the table doesn't exist, return 0
        return 0;
    } catch (\Exception $e) {
        \Log::warning('Error calculating bundle sales: ' . $e->getMessage());
        return 0;
    }
}

public function sales(Request $request)
{
    DB::statement("
        DELETE t1
        FROM rbotransactionsalestrans t1
        JOIN rbotransactionsalestrans t2
        ON t1.transactionid = t2.transactionid
        AND t1.linenum = t2.linenum
        AND t1.id > t2.id
    ");

    DB::statement("
        DELETE t1
        FROM rbotransactiontables t1
        JOIN rbotransactiontables t2
        ON t1.transactionid = t2.transactionid
        AND t1.id > t2.id
    ");
    
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string'  
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    // DB::statement("
    //     DELETE t1
    //     FROM rbotransactionsalestrans t1
    //     JOIN rbotransactionsalestrans t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.linenum = t2.linenum
    //     AND t1.id > t2.id
    // ");

    // DB::statement("
    //     DELETE t1
    //     FROM rbotransactiontables t1
    //     JOIN rbotransactiontables t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.id > t2.id
    // ");

    $query = rbotransactiontables::select(
        'rbotransactiontables.store as storename', 
        DB::raw('SUM(rbotransactiontables.netamount) as netamount'),
        DB::raw('SUM(rbotransactiontables.discamount) as discamount'),
        DB::raw('SUM(rbotransactiontables.grossamount) as grossamount')
    )
    ->groupBy('rbotransactiontables.store')
    ->join('rbostoretables', 'rbotransactiontables.store', '=', 'rbostoretables.NAME');

    if ($request->filled(['startDate', 'endDate'])) {
        $startDate = Carbon::parse($request->startDate)->startOfDay();  
        $endDate = Carbon::parse($request->endDate)->endOfDay();  
        $query->whereBetween('rbotransactiontables.createddate', [$startDate, $endDate]);
    }

    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactiontables.store', $userStoreId); 
    }

    $stores = rbostoretables::select('STOREID', 'NAME')
        ->orderBy('NAME')
        ->get();

    $ec = $query->orderBy('createddate', 'desc')->get();

    $totals = [
        'grossamount' => $ec->sum('grossamount'),
        'discamount' => $ec->sum('discamount'),
        'netamount' => $ec->sum('netamount'),
    ];

    return Inertia::render('Reports/Sales', [
        'ec' => $ec,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? [],
        ],
    ]);
}

/* public function tsales(Request $request)
{
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string',
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    // Clean up duplicate records
    DB::statement("
        DELETE t1
        FROM rbotransactionsalestrans t1
        JOIN rbotransactionsalestrans t2
        ON t1.transactionid = t2.transactionid
        AND t1.linenum = t2.linenum
        AND t1.id > t2.id
    ");

    DB::statement("
        DELETE t1
        FROM rbotransactiontables t1
        JOIN rbotransactiontables t2
        ON t1.transactionid = t2.transactionid
        AND t1.id > t2.id
    ");

    $query = DB::table('rbotransactionsalestrans')
        ->select(
            DB::raw('DATE(rbotransactionsalestrans.createddate) as createddate'),
            DB::raw('TIME(rbotransactionsalestrans.createddate) as timeonly'),
            'rbotransactionsalestrans.transactionid',
            DB::raw("REGEXP_REPLACE(rbotransactionsalestrans.receiptid, '[^0-9]', '') as receiptid"),
            DB::raw('rbotransactionsalestrans.grossamount as total_grossamount'),
            DB::raw('rbotransactionsalestrans.costamount as total_costamount'),
            'rbotransactionsalestrans.paymentmethod',
            'rbotransactionsalestrans.itemgroup',
            'rbotransactionsalestrans.itemname',
            'rbotransactionsalestrans.qty',
            'rbotransactionsalestrans.price',
            'rbotransactionsalestrans.custaccount',
            'rbotransactionsalestrans.discofferid',
            DB::raw('rbotransactionsalestrans.discamount as total_discamount'),
            DB::raw('rbotransactionsalestrans.price / 1.12 as total_costprice'),
            DB::raw('rbotransactionsalestrans.netamount as total_netamount'),
            DB::raw('rbotransactionsalestrans.netamountnotincltax as vatablesales'),
            DB::raw('rbotransactionsalestrans.taxinclinprice as vat'),
            'rbotransactionsalestrans.store as storename',
            'rbotransactionsalestrans.staff',
            'rbotransactionsalestrans.remarks',
            
            // Get transaction totals for percentage calculation
            DB::raw('rbt.netamount as transaction_total_netamount'),
            
            // Improved payment method calculations
            DB::raw('
                CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CHARGE" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.charge, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CHARGE" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END as charge
            '),
            DB::raw('
                CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "GCASH" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.gcash, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "GCASH" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END as gcash
            '),
            DB::raw('
                CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "PAYMAYA" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.paymaya, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "PAYMAYA" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END as paymaya
            '),
            DB::raw('
                CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CASH" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.cash, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CASH" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END as cash
            '),
            DB::raw('
                CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CARD" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.card, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CARD" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END as card
            '),
            DB::raw('
                CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "LOYALTYCARD" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.loyaltycard, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "LOYALTYCARD" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END as loyaltycard
            '),
            DB::raw('
                CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "FOODPANDA" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.foodpanda, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "FOODPANDA" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END as foodpanda
            '),
            DB::raw('
                CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "GRABFOOD" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.grabfood, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "GRABFOOD" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END as grabfood
            '),
            DB::raw('
                CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "REPRESENTATION" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.representation, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "REPRESENTATION" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END as representation
            '),
            
            // Commission calculation based on remarks
            DB::raw("
                CASE 
                    WHEN LOWER(TRIM(rbotransactionsalestrans.remarks)) = 'qrph' 
                    THEN rbotransactionsalestrans.netamount - 0.75
                    ELSE 0 
                END as commission
            "),
            
            // RD Discount - include '20% EMPLOYEE DISCOUNT'
            DB::raw("
                CASE 
                    WHEN rbotransactionsalestrans.discofferid LIKE '%SENIOR%' 
                      OR rbotransactionsalestrans.discofferid LIKE '%PWD%'
                      OR rbotransactionsalestrans.discofferid = '25% One Day Before'
                      OR rbotransactionsalestrans.discofferid = '20% EMPLOYEE DISCOUNT'
                      OR rbotransactionsalestrans.discofferid = '40% OFF'
                      OR rbotransactionsalestrans.discofferid = '50% OFF'
                    THEN rbotransactionsalestrans.discamount 
                    ELSE 0 
                END as rddisc
            "),
            
            // Marketing Discount - exclude '20% EMPLOYEE DISCOUNT'
            DB::raw("
                CASE 
                    WHEN rbotransactionsalestrans.discofferid IS NOT NULL 
                      AND rbotransactionsalestrans.discofferid != ''
                      AND rbotransactionsalestrans.discofferid NOT LIKE '%SENIOR%' 
                      AND rbotransactionsalestrans.discofferid NOT LIKE '%PWD%'
                      AND rbotransactionsalestrans.discofferid != '25% One Day Before'
                      AND rbotransactionsalestrans.discofferid != '20% EMPLOYEE DISCOUNT'
                      AND rbotransactionsalestrans.discofferid != '40% OFF'
                      AND rbotransactionsalestrans.discofferid != '50% OFF'
                    THEN rbotransactionsalestrans.discamount 
                    ELSE 0 
                END as mrktgdisc
            "),
            
            // Product categories based on your requirements
            DB::raw("
                CASE 
                    WHEN UPPER(rbotransactionsalestrans.itemgroup) LIKE '%BW%' 
                    THEN rbotransactionsalestrans.grossamount 
                    ELSE 0 
                END as bw_products
            "),
            DB::raw("
                CASE 
                    WHEN UPPER(rbotransactionsalestrans.itemgroup) NOT LIKE '%BW%' 
                      AND UPPER(rbotransactionsalestrans.itemname) != 'PARTYCAKES'
                    THEN rbotransactionsalestrans.grossamount 
                    ELSE 0 
                END as merchandise
            "),
            DB::raw("
                CASE 
                    WHEN UPPER(rbotransactionsalestrans.itemname) = 'PARTY CAKES' 
                    THEN rbotransactionsalestrans.grossamount 
                    ELSE 0 
                END as partycakes
            ")
        )
        ->leftJoin('rbostoretables', 'rbotransactionsalestrans.store', '=', 'rbostoretables.STOREID')
        ->leftJoin('rbotransactiontables as rbt', function($join) {
            $join->on('rbotransactionsalestrans.transactionid', '=', 'rbt.transactionid')
                 ->on('rbotransactionsalestrans.store', '=', 'rbt.store');
        })
        ->orderBy('rbotransactionsalestrans.transactionid', 'DESC');

    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            $request->startDate . ' 00:00:00',
            $request->endDate . ' 23:59:59',
        ]);
    }

    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactionsalestrans.store', $userStoreId);
    }

    $stores = rbostoretables::select('STOREID', 'NAME')
        ->orderBy('NAME')
        ->get();

    $ec = $query->orderBy('createddate', 'desc')->get();

    $totals = [
        'grossamount' => $ec->sum('total_grossamount'),
        'discamount' => $ec->sum('total_discamount'),
        'netamount' => $ec->sum('total_netamount'),
        'commission' => $ec->sum('commission'),
    ];

    return Inertia::render('Reports/TSales', [
        'ec' => $ec,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? [],
        ],
    ]);
} */


public function tsales(Request $request)
{
    // Increase memory limit to prevent exhaustion on large datasets
    ini_set('memory_limit', '512M');
    // Set longer execution time for complex queries
    ini_set('max_execution_time', 300);
    
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string',
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    DB::statement("
        DELETE t1
        FROM rbotransactionsalestrans t1
        JOIN rbotransactionsalestrans t2
        ON t1.transactionid = t2.transactionid
        AND t1.linenum = t2.linenum
        AND t1.id > t2.id
    ");

    DB::statement("
        DELETE t1
        FROM rbotransactiontables t1
        JOIN rbotransactiontables t2
        ON t1.transactionid = t2.transactionid
        AND t1.id > t2.id
    ");

    if ($request->get('cleanup', false)) {
        DB::statement("
            DELETE t1
            FROM rbotransactionsalestrans t1
            JOIN rbotransactionsalestrans t2
            ON t1.transactionid = t2.transactionid
            AND t1.linenum = t2.linenum
            AND t1.id > t2.id
        ");

        DB::statement("
            DELETE t1
            FROM rbotransactiontables t1
            JOIN rbotransactiontables t2
            ON t1.transactionid = t2.transactionid
            AND t1.id > t2.id
        ");
    }

    $query = DB::table('rbotransactionsalestrans')
        ->select(
            DB::raw('DATE(rbotransactionsalestrans.createddate) as createddate'),
            DB::raw('TIME(rbotransactionsalestrans.createddate) as timeonly'),
            'rbotransactionsalestrans.transactionid',
            DB::raw("REGEXP_REPLACE(rbotransactionsalestrans.receiptid, '[^0-9]', '') as receiptid"),
            
            // Aggregate financial data for grouped records
            DB::raw('SUM(rbotransactionsalestrans.grossamount) as total_grossamount'),
            DB::raw('SUM(rbotransactionsalestrans.costamount) as total_costamount'),
            
            'rbotransactionsalestrans.paymentmethod',
            'rbotransactionsalestrans.itemgroup',
            'rbotransactionsalestrans.itemname',
            
            // Sum quantities for the same item
            DB::raw('SUM(rbotransactionsalestrans.qty) as qty'),
            
            'rbotransactionsalestrans.price',
            'rbotransactionsalestrans.custaccount',
            'rbotransactionsalestrans.discofferid',
            
            // Aggregate discount amounts
            DB::raw('SUM(rbotransactionsalestrans.discamount) as total_discamount'),
            DB::raw('SUM(rbotransactionsalestrans.price) / 1.12 as total_costprice'),
            DB::raw('SUM(rbotransactionsalestrans.netamount) as total_netamount'),
            DB::raw('SUM(rbotransactionsalestrans.netamountnotincltax) as vatablesales'),
            DB::raw('SUM(rbotransactionsalestrans.taxinclinprice) as vat'),
            
            // Use the store field directly since it contains store names
            'rbotransactionsalestrans.store as storename',
            'rbotransactionsalestrans.staff',
            'rbotransactionsalestrans.remarks',
            
            // Get transaction totals for percentage calculation
            DB::raw('rbt.netamount as transaction_total_netamount'),
            
            // Improved payment method calculations - aggregate amounts
            DB::raw('
                SUM(CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CHARGE" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.charge, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CHARGE" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END) as charge
            '),
            DB::raw('
                SUM(CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "GCASH" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.gcash, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "GCASH" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END) as gcash
            '),
            DB::raw('
                SUM(CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "PAYMAYA" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.paymaya, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "PAYMAYA" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END) as paymaya
            '),
            DB::raw('
                SUM(CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CASH" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.cash, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CASH" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END) as cash
            '),
            DB::raw('
                SUM(CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CARD" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.card, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "CARD" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END) as card
            '),
            DB::raw('
                SUM(CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "LOYALTYCARD" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.loyaltycard, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "LOYALTYCARD" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END) as loyaltycard
            '),
            DB::raw('
                SUM(CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "FOODPANDA" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.foodpanda, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "FOODPANDA" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END) as foodpanda
            '),
            DB::raw('
                SUM(CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "GRABFOOD" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.grabfood, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "GRABFOOD" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END) as grabfood
            '),
            DB::raw('
                SUM(CASE 
                    WHEN rbotransactionsalestrans.netamount < 0 THEN
                        CASE 
                            WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "REPRESENTATION" 
                            THEN rbotransactionsalestrans.netamount
                            ELSE 0 
                        END
                    WHEN rbt.netamount > 0 THEN
                        CASE
                            WHEN (COALESCE(rbt.charge, 0) + COALESCE(rbt.gcash, 0) + COALESCE(rbt.paymaya, 0) + 
                                  COALESCE(rbt.cash, 0) + COALESCE(rbt.card, 0) + COALESCE(rbt.loyaltycard, 0) + 
                                  COALESCE(rbt.foodpanda, 0) + COALESCE(rbt.grabfood, 0) + COALESCE(rbt.representation, 0)) = rbt.netamount
                            THEN ROUND((rbotransactionsalestrans.netamount / rbt.netamount) * COALESCE(rbt.representation, 0), 2)
                            ELSE 
                                CASE 
                                    WHEN UPPER(rbotransactionsalestrans.paymentmethod) = "REPRESENTATION" 
                                    THEN rbotransactionsalestrans.netamount
                                    ELSE 0 
                                END
                        END
                    ELSE 0 
                END) as representation
            '),
            
            // Commission calculation based on remarks - aggregate
            DB::raw("
                SUM(CASE 
                    WHEN LOWER(TRIM(rbotransactionsalestrans.remarks)) = 'qrph' 
                    THEN rbotransactionsalestrans.netamount - 0.75
                    ELSE 0 
                END) as commission
            "),
            
            // RD Discount - include '20% EMPLOYEE DISCOUNT' - aggregate
            DB::raw("
                SUM(CASE 
                    WHEN rbotransactionsalestrans.discofferid LIKE '%SENIOR%' 
                      OR rbotransactionsalestrans.discofferid LIKE '%PWD%'
                      OR rbotransactionsalestrans.discofferid LIKE '%25%'
                      OR rbotransactionsalestrans.discofferid = '20% EMPLOYEE DISCOUNT'
                      OR rbotransactionsalestrans.discofferid = '40% OFF'
                      OR rbotransactionsalestrans.discofferid = '50% OFF'
                    THEN rbotransactionsalestrans.discamount 
                    ELSE 0 
                END) as rddisc
            "),
            
            // Marketing Discount - exclude '20% EMPLOYEE DISCOUNT' - aggregate
            DB::raw("
                SUM(CASE 
                    WHEN rbotransactionsalestrans.discofferid IS NOT NULL 
                      AND rbotransactionsalestrans.discofferid != ''
                      AND rbotransactionsalestrans.discofferid NOT LIKE '%SENIOR%' 
                      AND rbotransactionsalestrans.discofferid NOT LIKE '%PWD%'
                      AND rbotransactionsalestrans.discofferid NOT LIKE '%25%'
                      AND rbotransactionsalestrans.discofferid != '20% EMPLOYEE DISCOUNT'
                      AND rbotransactionsalestrans.discofferid != '40% OFF'
                      AND rbotransactionsalestrans.discofferid != '50% OFF'
                    THEN rbotransactionsalestrans.discamount 
                    ELSE 0 
                END) as mrktgdisc
            "),
            
            // Product categories based on your requirements - aggregate
            DB::raw("
                SUM(CASE 
                    WHEN UPPER(rbotransactionsalestrans.itemgroup) LIKE '%BW%' 
                    AND UPPER(rbotransactionsalestrans.itemname) != 'PARTY CAKES'
                    THEN rbotransactionsalestrans.grossamount 
                    ELSE 0 
                END) as bw_products
            "),
            DB::raw("
                SUM(CASE 
                    WHEN UPPER(rbotransactionsalestrans.itemgroup) NOT LIKE '%BW%' 
                      AND UPPER(rbotransactionsalestrans.itemname) != 'PARTY CAKES'
                    THEN rbotransactionsalestrans.grossamount 
                    ELSE 0 
                END) as merchandise
            "),
            DB::raw("
                SUM(CASE 
                    WHEN UPPER(rbotransactionsalestrans.itemname) = 'PARTY CAKES' 
                    THEN rbotransactionsalestrans.grossamount 
                    ELSE 0 
                END) as partycakes
            ")
        )
        ->leftJoin('rbotransactiontables as rbt', function($join) {
            $join->on('rbotransactionsalestrans.transactionid', '=', 'rbt.transactionid')
                 ->on('rbotransactionsalestrans.store', '=', 'rbt.store');
        });

    // **KEY OPTIMIZATION: Default to current date if no filters provided**
    if (!$request->filled(['startDate', 'endDate'])) {
        // If no date filters provided, default to today's data only
        $today = now()->format('Y-m-d');
        $query->whereBetween('rbotransactionsalestrans.createddate', [
            $today . ' 00:00:00',
            $today . ' 23:59:59',
        ]);
    } else {
        // Memory protection: Limit date range to prevent excessive data loading
        $startDate = \Carbon\Carbon::parse($request->startDate);
        $endDate = \Carbon\Carbon::parse($request->endDate);
        
        // Restrict to maximum 14 days to prevent memory exhaustion on complex queries
        if ($startDate->diffInDays($endDate) > 14) {
            $endDate = $startDate->copy()->addDays(14);
        }
        
        // Use provided date filters with memory protection
        $query->whereBetween('rbotransactionsalestrans.createddate', [
            $startDate->format('Y-m-d') . ' 00:00:00',
            $endDate->format('Y-m-d') . ' 23:59:59',
        ]);
    }

    // **FIXED: Store filtering - Filter directly by store names**
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores') && is_array($request->stores) && !empty($request->stores)) {
            // Filter directly by store names since rbotransactionsalestrans.store contains store names
            $query->whereIn('rbotransactionsalestrans.store', $request->stores);
        }
    } else {
        $query->where('rbotransactionsalestrans.store', $userStoreId);
    }

    // GROUP BY to remove redundant data based on receiptid, itemname, qty
    $query->groupBy([
        'rbotransactionsalestrans.receiptid',
        'rbotransactionsalestrans.itemname',
        'rbotransactionsalestrans.qty',
        'rbotransactionsalestrans.transactionid',
        'rbotransactionsalestrans.createddate',
        'rbotransactionsalestrans.paymentmethod',
        'rbotransactionsalestrans.itemgroup',
        'rbotransactionsalestrans.price',
        'rbotransactionsalestrans.custaccount',
        'rbotransactionsalestrans.discofferid',
        'rbotransactionsalestrans.store',
        'rbotransactionsalestrans.staff',
        'rbotransactionsalestrans.remarks',
        'rbt.netamount',
        'rbt.charge',
        'rbt.gcash',
        'rbt.paymaya',
        'rbt.cash',
        'rbt.card',
        'rbt.loyaltycard',
        'rbt.foodpanda',
        'rbt.grabfood',
        'rbt.representation'
    ]);

    // Order by latest first for better user experience
    $query->orderBy('rbotransactionsalestrans.createddate', 'DESC')
          ->orderBy('rbotransactionsalestrans.transactionid', 'DESC');

    // Get stores list - Get unique store names from transactions table instead of rbostoretables
    $stores = DB::table('rbotransactionsalestrans')
        ->select('store as NAME')
        ->distinct()
        ->whereNotNull('store')
        ->where('store', '!=', '')
        ->orderBy('store')
        ->get()
        ->map(function($store) {
            return [
                'STOREID' => $store->NAME, // Use store name as both ID and NAME
                'NAME' => $store->NAME
            ];
        });

    // Memory-efficient execution with chunking to prevent exhaustion
    $page = $request->get('page', 1);
    $perPage = 100; // Drastically reduce chunk size to prevent memory issues
    
    try {
        $ec = $query->paginate($perPage, ['*'], 'page', $page);
    } catch (\Exception $e) {
        // If memory issues persist, return a simplified error message
        if (strpos($e->getMessage(), 'memory') !== false) {
            return Inertia::render('Reports/TSales', [
                'ec' => [],
                'stores' => [],
                'userRole' => $role,
                'totals' => ['grossamount' => 0, 'discamount' => 0, 'netamount' => 0, 'commission' => 0],
                'filters' => ['startDate' => $request->startDate, 'endDate' => $request->endDate],
                'error' => 'Date range too large. Please select a smaller date range (max 7 days).',
                'pagination' => ['current_page' => 1, 'total' => 0, 'per_page' => 100, 'last_page' => 1]
            ]);
        }
        throw $e;
    }
    
    // Calculate totals more efficiently using a separate lightweight query
    $totalsResult = DB::table('rbotransactionsalestrans')
        ->leftJoin('rbotransactiontables as rbt', function($join) {
            $join->on('rbotransactionsalestrans.transactionid', '=', 'rbt.transactionid')
                 ->on('rbotransactionsalestrans.store', '=', 'rbt.store');
        })
        ->select([
            DB::raw('SUM(rbotransactionsalestrans.grossamount) as total_grossamount'),
            DB::raw('SUM(rbotransactionsalestrans.discamount) as total_discamount'), 
            DB::raw('SUM(rbotransactionsalestrans.netamount) as total_netamount'),
            DB::raw('SUM(rbotransactionsalestrans.netamount * 0.07) as commission')
        ]);
    
    // Apply same filters as main query for totals
    if (!$request->filled(['startDate', 'endDate'])) {
        $today = now()->format('Y-m-d');
        $totalsResult->whereBetween('rbotransactionsalestrans.createddate', [
            $today . ' 00:00:00',
            $today . ' 23:59:59',
        ]);
    } else {
        $startDate = \Carbon\Carbon::parse($request->startDate);
        $endDate = \Carbon\Carbon::parse($request->endDate);
        if ($startDate->diffInDays($endDate) > 14) {
            $endDate = $startDate->copy()->addDays(14);
        }
        $totalsResult->whereBetween('rbotransactionsalestrans.createddate', [
            $startDate->format('Y-m-d') . ' 00:00:00',
            $endDate->format('Y-m-d') . ' 23:59:59',
        ]);
    }
    
    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores') && is_array($request->stores) && !empty($request->stores)) {
            $totalsResult->whereIn('rbotransactionsalestrans.store', $request->stores);
        }
    } else {
        $totalsResult->where('rbotransactionsalestrans.store', $userStoreId);
    }
    
    $totalsData = $totalsResult->first();
    $totals = [
        'grossamount' => $totalsData->total_grossamount ?? 0,
        'discamount' => $totalsData->total_discamount ?? 0,
        'netamount' => $totalsData->total_netamount ?? 0,
        'commission' => $totalsData->commission ?? 0,
    ];

    // Set default filter values for frontend
    $defaultFilters = [
        'startDate' => $request->startDate ?: now()->format('Y-m-d'),
        'endDate' => $request->endDate ?: now()->format('Y-m-d'),
        'selectedStores' => $request->stores ?? [],
    ];

    return Inertia::render('Reports/TSales', [
        'ec' => $ec->items(), // Extract items from paginated data to maintain frontend compatibility
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => $defaultFilters,
        'isInitialLoad' => !$request->filled(['startDate', 'endDate']), // Flag to indicate if this is initial load
        'pagination' => [
            'current_page' => $ec->currentPage(),
            'total' => $ec->total(),
            'per_page' => $ec->perPage(),
            'last_page' => $ec->lastPage(),
        ]
    ]);
}


public function itemsales(Request $request)
{
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string',
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    //  DB::statement("
    //     DELETE t1
    //     FROM rbotransactionsalestrans t1
    //     JOIN rbotransactionsalestrans t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.linenum = t2.linenum
    //     AND t1.id > t2.id
    // ");

    // DB::statement("
    //     DELETE t1
    //     FROM rbotransactiontables t1
    //     JOIN rbotransactiontables t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.id > t2.id
    // "); 

    $query = DB::table('rbotransactionsalestrans')
    ->select(
        'rbotransactionsalestrans.store as storename',
        'rbotransactionsalestrans.itemname',
        'rbotransactionsalestrans.itemgroup',
        'rbotransactionsalestrans.price as price', 
        DB::raw('DATE(rbotransactionsalestrans.createddate) as createddate'),
        DB::raw('SUM(rbotransactionsalestrans.grossamount) as total_grossamount'),
        DB::raw('SUM(rbotransactionsalestrans.costamount) as total_costamount'),
        DB::raw('SUM(rbotransactionsalestrans.qty) as qty'),
        DB::raw('SUM(rbotransactionsalestrans.discamount) as total_discamount'),
        DB::raw('SUM(rbotransactionsalestrans.qty * rbotransactionsalestrans.price / 1.12) as total_costprice'), 
        DB::raw('SUM(rbotransactionsalestrans.netamount) as total_netamount'),
        DB::raw('SUM(rbotransactionsalestrans.netamountnotincltax) as vatablesales'),
        DB::raw('SUM(rbotransactionsalestrans.taxinclinprice) as vat')
    )
    ->leftJoin('rbostoretables', 'rbotransactionsalestrans.store', '=', 'rbostoretables.STOREID')
    /* ->where('rbotransactionsalestrans.itemgroup', '!=', 'BW PROMO') */
    ->groupBy(
        'rbotransactionsalestrans.itemname',
        DB::raw('DATE(rbotransactionsalestrans.createddate)'), 
        'rbotransactionsalestrans.store',
        'rbotransactionsalestrans.itemgroup',
        'rbotransactionsalestrans.price'
    );

    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            $request->startDate . ' 00:00:00',
            $request->endDate . ' 23:59:59',
        ]);
    }

    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactionsalestrans.store', $userStoreId);
    }

    $stores = rbostoretables::select('STOREID', 'NAME')
        ->orderBy('NAME')
        ->get();

    $ec = $query->orderBy('createddate', 'desc')->get();

    $totals = [
        'grossamount' => $ec->sum('grossamount'),
        'discamount' => $ec->sum('discamount'),
        'netamount' => $ec->sum('netamount'),
    ];

    return Inertia::render('Reports/ItemSales', [
        'ec' => $ec,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? [],
        ],
    ]);
}


public function md(Request $request)
{
    $request->validate([
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'stores' => 'nullable|array',
        'stores.*' => 'string',
    ]);

    $role = Auth::user()->role;
    $userStoreId = Auth::user()->storeid;

    // DB::statement("
    //     DELETE t1
    //     FROM rbotransactionsalestrans t1
    //     JOIN rbotransactionsalestrans t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.linenum = t2.linenum
    //     AND t1.id > t2.id
    // ");

    // DB::statement("
    //     DELETE t1
    //     FROM rbotransactiontables t1
    //     JOIN rbotransactiontables t2
    //     ON t1.transactionid = t2.transactionid
    //     AND t1.id > t2.id
    // "); 

    $query = DB::table('rbotransactionsalestrans')
    ->select(
        DB::raw('DATE(rbotransactionsalestrans.createddate) as createddate'),
        'rbotransactionsalestrans.store as storename',
        'rbotransactionsalestrans.discofferid',
        DB::raw('TIME(rbotransactionsalestrans.createddate) as timeonly'),
        DB::raw('SUM(rbotransactionsalestrans.grossamount) as total_grossamount'),
        DB::raw('SUM(rbotransactionsalestrans.costamount) as total_costamount'),
        DB::raw('SUM(rbotransactionsalestrans.qty) as qty'),
        DB::raw('SUM(rbotransactionsalestrans.qty * rbotransactionsalestrans.price) as price'), 
        DB::raw('SUM(rbotransactionsalestrans.discamount) as total_discamount'),
        DB::raw('SUM(rbotransactionsalestrans.qty * rbotransactionsalestrans.price / 1.12) as total_costprice'), 
        DB::raw('SUM(rbotransactionsalestrans.netamount) as total_netamount'),
        DB::raw('SUM(rbotransactionsalestrans.netamountnotincltax) as vatablesales'),
        DB::raw('SUM(rbotransactionsalestrans.taxinclinprice) as vat')
    )
    ->leftJoin('rbostoretables', 'rbotransactionsalestrans.store', '=', 'rbostoretables.STOREID')
    ->where('rbotransactionsalestrans.discofferid', '!=', null)
    ->groupBy(
        'rbotransactionsalestrans.discofferid',
        'rbotransactionsalestrans.createddate',
        'rbotransactionsalestrans.store' 
    );

    if ($request->filled(['startDate', 'endDate'])) {
        $query->whereBetween('createddate', [
            $request->startDate . ' 00:00:00',
            $request->endDate . ' 23:59:59',
        ]);
    }

    if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
        if ($request->filled('stores')) {
            $query->whereIn('rbostoretables.NAME', $request->stores);
        }
    } else {
        $query->where('rbotransactionsalestrans.store', $userStoreId);
    }

    $stores = rbostoretables::select('STOREID', 'NAME')
        ->orderBy('NAME')
        ->get();

    $ec = $query->orderBy('createddate', 'desc')->get();

    $totals = [
        'grossamount' => $ec->sum('grossamount'),
        'discamount' => $ec->sum('discamount'),
        'netamount' => $ec->sum('netamount'),
    ];

    return Inertia::render('Reports/MarketingDiscount', [
        'ec' => $ec,
        'stores' => $stores,
        'userRole' => $role,
        'totals' => $totals,
        'filters' => [
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'selectedStores' => $request->stores ?? [],
        ],
    ]);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
 * Adjust item count with remarks
 */
public function adjustItemCount(Request $request)
{
    try {
        $request->validate([
            'id' => 'required|integer',
            'adjustment_value' => 'required|numeric',
            'adjustment_type' => 'required|in:add,subtract,set',
            'remarks' => 'required|string|max:500'
        ]);

        DB::beginTransaction();

        // Find the inventory summary record
        $inventorySummary = DB::table('inventory_summaries')->where('id', $request->id)->first();
        
        if (!$inventorySummary) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Inventory record not found'
            ], 404);
        }

        $currentItemCount = (float) ($inventorySummary->item_count ?? 0);
        $adjustmentValue = (float) $request->adjustment_value;
        $newItemCount = $currentItemCount;

        // Calculate new item count based on adjustment type
        switch ($request->adjustment_type) {
            case 'add':
                $newItemCount = $currentItemCount + $adjustmentValue;
                break;
            case 'subtract':
                $newItemCount = $currentItemCount - $adjustmentValue;
                break;
            case 'set':
                $newItemCount = $adjustmentValue;
                break;
        }

        // Ensure item count doesn't go negative
        if ($newItemCount < 0) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Item count cannot be negative'
            ], 400);
        }

        // Update inventory summary
        DB::table('inventory_summaries')
            ->where('id', $request->id)
            ->update([
                'item_count' => $newItemCount,
                'remarks' => $request->remarks,
                'updated_at' => now()
            ]);

        // Recalculate variance after adjustment
        $ending = (float) ($inventorySummary->ending ?? 0);
        $newVariance = $ending - $newItemCount;
        
        DB::table('inventory_summaries')
            ->where('id', $request->id)
            ->update([
                'variance' => $newVariance
            ]);

        // Check if inventory_adjustments table exists before logging
        if (Schema::hasTable('inventory_adjustments')) {
            // Log the adjustment for audit trail
            DB::table('inventory_adjustments')->insert([
                'inventory_summary_id' => $request->id,
                'itemid' => $inventorySummary->itemid,
                'storename' => $inventorySummary->storename,
                'report_date' => $inventorySummary->report_date,
                'old_item_count' => $currentItemCount,
                'new_item_count' => $newItemCount,
                'adjustment_value' => $adjustmentValue,
                'adjustment_type' => $request->adjustment_type,
                'remarks' => $request->remarks,
                'adjusted_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Item count adjusted successfully',
            'data' => [
                'old_item_count' => $currentItemCount,
                'new_item_count' => $newItemCount,
                'new_variance' => $newVariance
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error adjusting item count: ' . $e->getMessage(), [
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while adjusting item count. Please try again.'
        ], 500);
    }
}

/**
 * Get adjustment history for an item
 */
public function getAdjustmentHistory(Request $request)
{
    try {
        $request->validate([
            'itemid' => 'required|string',
            'storename' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date'
        ]);

        // Check if inventory_adjustments table exists
        if (!Schema::hasTable('inventory_adjustments')) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No adjustment history available'
            ]);
        }

        $query = DB::table('inventory_adjustments')
            ->where('itemid', $request->itemid)
            ->where('storename', $request->storename)
            ->leftJoin('users', 'inventory_adjustments.adjusted_by', '=', 'users.id')
            ->select(
                'inventory_adjustments.*',
                'users.name as adjusted_by_name'
            );

        if ($request->filled('start_date')) {
            $query->whereDate('inventory_adjustments.created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('inventory_adjustments.created_at', '<=', $request->end_date);
        }

        $adjustments = $query->orderBy('inventory_adjustments.created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $adjustments
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error fetching adjustment history: ' . $e->getMessage(), [
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while fetching adjustment history'
        ], 500);
    }
}

public function syncInventoryVariance(Request $request)
{
    try {
        $request->validate([
            'sync_date' => 'required|date',
            'store_name' => 'nullable|string'
        ]);

        $role = Auth::user()->role;
        $userStoreId = Auth::user()->storeid;
        $syncDate = $request->sync_date;
        
        // Determine which store to sync based on user role
        $storeName = null;
        if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
            $storeName = $request->store_name;
            if (!$storeName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store name is required for admin users'
                ], 422);
            }
        } else {
            // For store users, use their assigned store
            $storeRecord = rbostoretables::where('STOREID', $userStoreId)->first();
            $storeName = $storeRecord ? $storeRecord->NAME : $userStoreId;
        }

        Log::info('Starting inventory variance sync', [
            'sync_date' => $syncDate,
            'store_name' => $storeName,
            'user_id' => Auth::id(),
            'user_role' => $role
        ]);

        DB::beginTransaction();

        // Check if there's count data to sync (either from inventory_summaries or stockcountingtrans)
        $existingRecords = DB::table('inventory_summaries')
            ->where('storename', $storeName)
            ->whereDate('report_date', $syncDate)
            ->count();

        // If no inventory_summaries records exist, check if we have stockcountingtrans data to work with
        if ($existingRecords === 0) {
            $stockCountData = DB::table('stockcountingtrans')
                ->where('STORENAME', $storeName)
                ->whereDate('TRANSDATE', $syncDate)
                ->count();

            if ($stockCountData === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "No inventory or count data found for {$storeName} on {$syncDate}. Please ensure data exists for this date."
                ], 404);
            }

            // Create placeholder inventory_summaries records from stockcountingtrans data
            try {
                Log::info('Creating inventory summary records from stockcountingtrans data', [
                    'store_name' => $storeName,
                    'sync_date' => $syncDate
                ]);

                // Get unique items from stockcountingtrans for this store and date
                $itemsToCreate = DB::table('stockcountingtrans')
                    ->select('ITEMID', 'ITEMNAME')
                    ->where('STORENAME', $storeName)
                    ->whereDate('TRANSDATE', $syncDate)
                    ->distinct()
                    ->get();

                foreach ($itemsToCreate as $item) {
                    DB::table('inventory_summaries')->insertOrIgnore([
                        'itemid' => $item->ITEMID,
                        'itemname' => $item->ITEMNAME ?? 'Unknown',
                        'storename' => $storeName,
                        'report_date' => $syncDate,
                        'beginning' => 0,
                        'received_delivery' => 0,
                        'stock_transfer' => 0,
                        'sales' => 0,
                        'bundle_sales' => 0,
                        'throw_away' => 0,
                        'early_molds' => 0,
                        'pull_out' => 0,
                        'rat_bites' => 0,
                        'ant_bites' => 0,
                        'item_count' => 0,
                        'ending' => 0,
                        'variance' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                Log::info('Created inventory summary placeholder records', [
                    'count' => count($itemsToCreate),
                    'store_name' => $storeName,
                    'sync_date' => $syncDate
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to create inventory summary records from stockcountingtrans', [
                    'error' => $e->getMessage(),
                    'store_name' => $storeName,
                    'sync_date' => $syncDate
                ]);
                
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Failed to prepare inventory records for sync: " . $e->getMessage()
                ], 500);
            }
        }

        // Use the optimized temporary table approach for better performance
        Log::info('Starting optimized sync process with temporary tables');
        
        // Step 1: Create temporary tables for calculations
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_beginning");
        DB::statement("
            CREATE TEMPORARY TABLE temp_beginning AS
            SELECT itemid, counted as beginning_qty
            FROM stockcountingtrans 
            WHERE storename = ?
              AND CAST(TRANSDATE AS DATE) = DATE_SUB(?, INTERVAL 1 DAY)  
              AND counted != 0
        ", [$storeName, $syncDate]);

        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_received");
        DB::statement("
            CREATE TEMPORARY TABLE temp_received AS
            SELECT itemid, SUM(receivedcount) as received_qty
            FROM stockcountingtrans 
            WHERE storename = ?
              AND CAST(TRANSDATE AS DATE) = ?
              AND receivedcount != 0
            GROUP BY itemid
        ", [$storeName, $syncDate]);

        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_direct_sales");
        DB::statement("
            CREATE TEMPORARY TABLE temp_direct_sales AS
            SELECT itemid, SUM(qty) as sales_qty
            FROM rbotransactionsalestrans 
            WHERE store = ?
              AND CAST(CREATEDDATE AS DATE) = ?
            GROUP BY itemid
        ", [$storeName, $syncDate]);

        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_bundle_sales");
        DB::statement("
            CREATE TEMPORARY TABLE temp_bundle_sales AS
            SELECT 
                il.child_itemid as itemid,
                SUM(il.quantity * rts.qty) as bundle_qty
            FROM item_links il
            INNER JOIN rbotransactionsalestrans rts ON il.parent_itemid = rts.itemid
            LEFT JOIN inventtables inv ON inv.itemid = il.child_itemid
            WHERE il.active = 1 
              AND rts.store = ?
              AND CAST(rts.createddate AS DATE) = ?
            GROUP BY il.child_itemid
        ", [$storeName, $syncDate]);

        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_throw_away");
        DB::statement("
            CREATE TEMPORARY TABLE temp_throw_away AS
            SELECT itemid, SUM(wastecount) as throw_away_qty
            FROM stockcountingtrans 
            WHERE storename = ?
              AND wastetype = 'Throw Away' 
              AND CAST(TRANSDATE AS DATE) = ?
              AND wastecount != 0
            GROUP BY itemid
        ", [$storeName, $syncDate]);

        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_pull_out");
        DB::statement("
            CREATE TEMPORARY TABLE temp_pull_out AS
            SELECT itemid, SUM(wastecount) as pull_out_qty
            FROM stockcountingtrans 
            WHERE storename = ?
              AND wastetype = 'Pull Out' 
              AND CAST(TRANSDATE AS DATE) = ?
              AND wastecount != 0
            GROUP BY itemid
        ", [$storeName, $syncDate]);

        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_rat_bites");
        DB::statement("
            CREATE TEMPORARY TABLE temp_rat_bites AS
            SELECT itemid, SUM(wastecount) as rat_bites_qty
            FROM stockcountingtrans 
            WHERE storename = ?
              AND wastetype LIKE '%Rat%' 
              AND CAST(TRANSDATE AS DATE) = ?
              AND wastecount != 0
            GROUP BY itemid
        ", [$storeName, $syncDate]);

        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_ant_bites");
        DB::statement("
            CREATE TEMPORARY TABLE temp_ant_bites AS
            SELECT itemid, SUM(wastecount) as ant_bites_qty
            FROM stockcountingtrans 
            WHERE storename = ?
              AND wastetype LIKE '%Ant%' 
              AND CAST(TRANSDATE AS DATE) = ?
              AND wastecount != 0
            GROUP BY itemid
        ", [$storeName, $syncDate]);

        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_early_molds");
        DB::statement("
            CREATE TEMPORARY TABLE temp_early_molds AS
            SELECT itemid, SUM(wastecount) as early_molds_qty
            FROM stockcountingtrans 
            WHERE storename = ?
              AND wastetype LIKE '%Molds%' 
              AND CAST(TRANSDATE AS DATE) = ?
              AND wastecount != 0
            GROUP BY itemid
        ", [$storeName, $syncDate]);

        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_staff_count");
        DB::statement("
            CREATE TEMPORARY TABLE temp_staff_count AS
            SELECT itemid, counted as staff_count_qty
            FROM stockcountingtrans 
            WHERE storename = ?
              AND CAST(TRANSDATE AS DATE) = ?
              AND counted != 0
        ", [$storeName, $syncDate]);

        // Step 2: Update inventory_summaries using temporary tables
        $beginningResult = DB::update("
            UPDATE inventory_summaries 
            INNER JOIN temp_beginning ON inventory_summaries.itemid = temp_beginning.itemid
            SET inventory_summaries.beginning = temp_beginning.beginning_qty,
                inventory_summaries.updated_at = CURRENT_TIMESTAMP
            WHERE inventory_summaries.storename = ?
              AND CAST(inventory_summaries.report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        $receivedResult = DB::update("
            UPDATE inventory_summaries 
            INNER JOIN temp_received ON inventory_summaries.itemid = temp_received.itemid
            SET inventory_summaries.received_delivery = temp_received.received_qty,
                inventory_summaries.updated_at = CURRENT_TIMESTAMP
            WHERE inventory_summaries.storename = ?
              AND CAST(inventory_summaries.report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        $salesResult = DB::update("
            UPDATE inventory_summaries 
            INNER JOIN temp_direct_sales ON inventory_summaries.itemid = temp_direct_sales.itemid
            SET inventory_summaries.sales = temp_direct_sales.sales_qty,
                inventory_summaries.updated_at = CURRENT_TIMESTAMP
            WHERE inventory_summaries.storename = ?
              AND CAST(inventory_summaries.report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        $bundleSalesResult = DB::update("
            UPDATE inventory_summaries 
            INNER JOIN temp_bundle_sales ON inventory_summaries.itemid = temp_bundle_sales.itemid
            SET inventory_summaries.bundle_sales = temp_bundle_sales.bundle_qty,
                inventory_summaries.updated_at = CURRENT_TIMESTAMP
            WHERE inventory_summaries.storename = ?
              AND CAST(inventory_summaries.report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        $throwAwayResult = DB::update("
            UPDATE inventory_summaries 
            INNER JOIN temp_throw_away ON inventory_summaries.itemid = temp_throw_away.itemid
            SET inventory_summaries.throw_away = temp_throw_away.throw_away_qty,
                inventory_summaries.updated_at = CURRENT_TIMESTAMP
            WHERE inventory_summaries.storename = ?
              AND CAST(inventory_summaries.report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        $pullOutResult = DB::update("
            UPDATE inventory_summaries 
            INNER JOIN temp_pull_out ON inventory_summaries.itemid = temp_pull_out.itemid
            SET inventory_summaries.pull_out = temp_pull_out.pull_out_qty,
                inventory_summaries.updated_at = CURRENT_TIMESTAMP
            WHERE inventory_summaries.storename = ?
              AND CAST(inventory_summaries.report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        $ratBitesResult = DB::update("
            UPDATE inventory_summaries 
            INNER JOIN temp_rat_bites ON inventory_summaries.itemid = temp_rat_bites.itemid
            SET inventory_summaries.rat_bites = temp_rat_bites.rat_bites_qty,
                inventory_summaries.updated_at = CURRENT_TIMESTAMP
            WHERE inventory_summaries.storename = ?
              AND CAST(inventory_summaries.report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        $antBitesResult = DB::update("
            UPDATE inventory_summaries 
            INNER JOIN temp_ant_bites ON inventory_summaries.itemid = temp_ant_bites.itemid
            SET inventory_summaries.ant_bites = temp_ant_bites.ant_bites_qty,
                inventory_summaries.updated_at = CURRENT_TIMESTAMP
            WHERE inventory_summaries.storename = ?
              AND CAST(inventory_summaries.report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        $earlyMoldsResult = DB::update("
            UPDATE inventory_summaries 
            INNER JOIN temp_early_molds ON inventory_summaries.itemid = temp_early_molds.itemid
            SET inventory_summaries.early_molds = temp_early_molds.early_molds_qty,
                inventory_summaries.updated_at = CURRENT_TIMESTAMP
            WHERE inventory_summaries.storename = ?
              AND CAST(inventory_summaries.report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        $staffCountResult = DB::update("
            UPDATE inventory_summaries 
            INNER JOIN temp_staff_count ON inventory_summaries.itemid = temp_staff_count.itemid
            SET inventory_summaries.item_count = temp_staff_count.staff_count_qty,
                inventory_summaries.updated_at = CURRENT_TIMESTAMP
            WHERE inventory_summaries.storename = ?
              AND CAST(inventory_summaries.report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        // Calculate and Update Ending Inventory for all records
        $endingResult = DB::update("
            UPDATE inventory_summaries 
            SET ending = (beginning + received_delivery + COALESCE(stock_transfer, 0)) 
                       - (sales + bundle_sales + throw_away + early_molds + pull_out + rat_bites + ant_bites),
                updated_at = CURRENT_TIMESTAMP
            WHERE storename = ?
              AND CAST(report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        // Calculate and Update Variance for all records
        $varianceResult = DB::update("
            UPDATE inventory_summaries 
            SET variance = ending - item_count,
                updated_at = CURRENT_TIMESTAMP
            WHERE storename = ?
              AND CAST(report_date AS DATE) = ?
        ", [$storeName, $syncDate]);

        // Clean up temporary tables
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_beginning");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_received");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_direct_sales");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_bundle_sales");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_throw_away");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_pull_out");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_rat_bites");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_ant_bites");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_early_molds");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_staff_count");

        // Log the sync operation in a sync_logs table if it exists
        if (Schema::hasTable('sync_logs')) {
            DB::table('sync_logs')->insert([
                'sync_type' => 'inventory_variance',
                'sync_date' => $syncDate,
                'store_name' => $storeName,
                'user_id' => Auth::id(),
                'affected_records' => $endingResult,
                'sync_details' => json_encode([
                    'beginning_updates' => $beginningResult,
                    'received_updates' => $receivedResult,
                    'sales_updates' => $salesResult,
                    'bundle_sales_updates' => $bundleSalesResult,
                    'throw_away_updates' => $throwAwayResult,
                    'pull_out_updates' => $pullOutResult,
                    'rat_bites_updates' => $ratBitesResult,
                    'ant_bites_updates' => $antBitesResult,
                    'early_molds_updates' => $earlyMoldsResult,
                    'staff_count_updates' => $staffCountResult,
                    'ending_calculations' => $endingResult,
                    'variance_calculations' => $varianceResult
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        DB::commit();

        $totalAffectedRows = $beginningResult + $receivedResult + $salesResult + $bundleSalesResult + 
                           $throwAwayResult + $pullOutResult + $ratBitesResult + $antBitesResult + 
                           $earlyMoldsResult + $staffCountResult + $endingResult + $varianceResult;

        Log::info('Inventory variance sync completed successfully', [
            'sync_date' => $syncDate,
            'store_name' => $storeName,
            'beginning_rows' => $beginningResult,
            'received_rows' => $receivedResult,
            'sales_rows' => $salesResult,
            'bundle_sales_rows' => $bundleSalesResult,
            'waste_rows' => [
                'throw_away' => $throwAwayResult,
                'pull_out' => $pullOutResult,
                'rat_bites' => $ratBitesResult,
                'ant_bites' => $antBitesResult,
                'early_molds' => $earlyMoldsResult
            ],
            'staff_count_rows' => $staffCountResult,
            'ending_rows' => $endingResult,
            'variance_rows' => $varianceResult,
            'total_affected_rows' => $totalAffectedRows
        ]);

        // Get summary of updated records to return
        $updatedRecords = DB::table('inventory_summaries')
            ->select([
                'itemid',
                'itemname',
                'beginning',
                'received_delivery',
                'sales',
                'bundle_sales',
                DB::raw('throw_away + early_molds + pull_out + rat_bites + ant_bites as total_waste'),
                'item_count',
                'ending',
                'variance',
                'updated_at'
            ])
            ->where('storename', $storeName)
            ->whereDate('report_date', $syncDate)
            ->orderBy('itemid')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Inventory variance sync completed successfully for {$storeName} on {$syncDate}",
            'data' => [
                'sync_date' => $syncDate,
                'store_name' => $storeName,
                'records_found' => $existingRecords,
                'records_updated' => $updatedRecords->count(),
                'affected_rows' => [
                    'beginning_inventory' => $beginningResult,
                    'received_delivery' => $receivedResult,
                    'direct_sales' => $salesResult,
                    'bundle_sales' => $bundleSalesResult,
                    'waste_types' => [
                        'throw_away' => $throwAwayResult,
                        'pull_out' => $pullOutResult,
                        'rat_bites' => $ratBitesResult,
                        'ant_bites' => $antBitesResult,
                        'early_molds' => $earlyMoldsResult
                    ],
                    'staff_count' => $staffCountResult,
                    'ending_calculations' => $endingResult,
                    'variance_calculations' => $varianceResult
                ],
                'total_affected_rows' => $totalAffectedRows,
                'summary_records' => $updatedRecords
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error during inventory variance sync', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'sync_date' => $request->sync_date ?? null,
            'store_name' => $request->store_name ?? null
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred during sync: ' . $e->getMessage()
        ], 500);
    }
}

// Add this companion method to create sync UI in the Vue component
public function getSyncStatus(Request $request)
{
    try {
        $request->validate([
            'sync_date' => 'required|date',
            'store_name' => 'nullable|string'
        ]);

        $role = Auth::user()->role;
        $userStoreId = Auth::user()->storeid;
        $syncDate = $request->sync_date;
        
        $storeName = null;
        if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
            $storeName = $request->store_name;
        } else {
            $storeRecord = rbostoretables::where('STOREID', $userStoreId)->first();
            $storeName = $storeRecord ? $storeRecord->NAME : $userStoreId;
        }

        // Check existing data status
        $inventoryCount = DB::table('inventory_summaries')
            ->where('report_date', $syncDate)
            ->where('storename', $storeName)
            ->count();

        $wasteCount = DB::table('stockcountingtrans')
            ->where('STORENAME', $storeName)
            ->whereDate('TRANSDATE', $syncDate)
            ->where('WASTECOUNT', '>', 0)
            ->count();

        $receivedCount = DB::table('stockcountingtrans')
            ->where('STORENAME', $storeName)
            ->whereDate('TRANSDATE', $syncDate)
            ->where('RECEIVEDCOUNT', '>', 0)
            ->count();

        $salesCount = DB::table('rbotransactionsalestrans')
            ->where('store', $storeName)
            ->whereDate('createddate', $syncDate)
            ->count();

        $lastSync = null;
        if (Schema::hasTable('sync_logs')) {
            $lastSync = DB::table('sync_logs')
                ->where('sync_type', 'inventory_variance')
                ->where('sync_date', $syncDate)
                ->where('store_name', $storeName)
                ->latest('created_at')
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'sync_date' => $syncDate,
                'store_name' => $storeName,
                'status' => [
                    'inventory_records' => $inventoryCount,
                    'waste_records' => $wasteCount,
                    'received_records' => $receivedCount,
                    'sales_records' => $salesCount,
                    'last_sync' => $lastSync,
                    'can_sync' => $inventoryCount > 0
                ]
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error getting sync status: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to get sync status'
        ], 500);
    }
}

public function getSyncStores()
{
    try {
        $role = Auth::user()->role;
        
        if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
            $stores = rbostoretables::select('STOREID', 'NAME')
                ->orderBy('NAME')
                ->get()
                ->map(function($store) {
                    return [
                        'id' => $store->STOREID,
                        'name' => $store->NAME
                    ];
                });
        } else {
            $userStoreId = Auth::user()->storeid;
            $store = rbostoretables::where('STOREID', $userStoreId)->first();
            $stores = $store ? [['id' => $store->STOREID, 'name' => $store->NAME]] : [];
        }

        return response()->json([
            'success' => true,
            'data' => $stores
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching sync stores: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch stores'
        ], 500);
    }
}

/**
 * Download count template based on stockcountingtrans structure
 */
public function downloadCountTemplate(Request $request)
{
    try {
        $request->validate([
            'import_date' => 'required|date',
            'store_name' => 'nullable|string'
        ]);

        $role = Auth::user()->role;
        $userStoreId = Auth::user()->storeid;
        $importDate = $request->import_date;
        
        // Determine which store based on user role
        $storeName = null;
        if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
            $storeName = $request->store_name;
            if (!$storeName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store name is required for admin users'
                ], 422);
            }
        } else {
            $storeRecord = rbostoretables::where('STOREID', $userStoreId)->first();
            $storeName = $storeRecord ? $storeRecord->NAME : $userStoreId;
        }

        Log::info('Generating count template', [
            'import_date' => $importDate,
            'store_name' => $storeName,
            'user_id' => Auth::id()
        ]);

        // Get items from inventtables joined with rboinventtables for department info
        $templateData = DB::table('inventtables')
            ->leftJoin('rboinventtables', 'inventtables.itemid', '=', 'rboinventtables.itemid')
            ->select([
                'inventtables.itemid as ITEMID',
                'inventtables.itemname as ITEMNAME',
                'rboinventtables.itemdepartment as ITEMDEPARTMENT',
                DB::raw("'{$storeName}' as STORENAME"),
                DB::raw("'{$importDate}' as TRANSDATE"),
                DB::raw('0 as COUNTED'),
                DB::raw('0 as WASTECOUNT'),
                DB::raw("'' as WASTETYPE"),
                DB::raw('0 as RECEIVEDCOUNT'),
                DB::raw('0 as TRANSFERCOUNT')
            ])
            ->whereNotNull('inventtables.itemid')
            ->orderBy('inventtables.itemid')
            ->get()
            ->toArray();

        if (empty($templateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No items found to create template'
            ], 404);
        }

        // Create Excel file
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = [
            'ITEMID', 'ITEMNAME', 'ITEMDEPARTMENT', 'STORENAME', 
            'TRANSDATE', 'COUNTED', 'WASTECOUNT', 'WASTETYPE', 
            'RECEIVEDCOUNT', 'TRANSFERCOUNT'
        ];
        
        $worksheet->fromArray($headers, null, 'A1');
        
        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '366092']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $worksheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        
        // Add data rows
        $row = 2;
        foreach ($templateData as $item) {
            $worksheet->setCellValue('A' . $row, $item->ITEMID);
            $worksheet->setCellValue('B' . $row, $item->ITEMNAME);
            $worksheet->setCellValue('C' . $row, $item->ITEMDEPARTMENT);
            $worksheet->setCellValue('D' . $row, $item->STORENAME);
            $worksheet->setCellValue('E' . $row, $item->TRANSDATE);
            $worksheet->setCellValue('F' . $row, $item->COUNTED);
            $worksheet->setCellValue('G' . $row, $item->WASTECOUNT);
            $worksheet->setCellValue('H' . $row, $item->WASTETYPE);
            $worksheet->setCellValue('I' . $row, $item->RECEIVEDCOUNT);
            $worksheet->setCellValue('J' . $row, $item->TRANSFERCOUNT);
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Add instructions sheet
        $instructionSheet = $spreadsheet->createSheet(1);
        $instructionSheet->setTitle('Instructions');
        $instructions = [
            ['Count Template Instructions'],
            [''],
            ['1. Fill in the COUNTED column with the actual count for each item'],
            ['2. Fill in WASTECOUNT for items that were wasted'],
            ['3. Fill in WASTETYPE for wasted items (e.g., "Throw Away", "Pull Out", "Early Molds", "Rat Bites", "Ant Bites")'],
            ['4. Fill in RECEIVEDCOUNT for items received on this date'],
            ['5. Fill in TRANSFERCOUNT for items transferred'],
            ['6. Save the file and import it back to the system'],
            [''],
            ['Notes:'],
            ['- Only fill in non-zero values'],
            ['- STORENAME and TRANSDATE should not be changed'],
            ['- ITEMID should not be changed'],
        ];
        $instructionSheet->fromArray($instructions, null, 'A1');
        $instructionSheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);
        $instructionSheet->getColumnDimension('A')->setAutoSize(true);
        
        // Set active sheet back to data
        $spreadsheet->setActiveSheetIndex(0);
        
        // Generate file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = "count_template_{$storeName}_{$importDate}.xlsx";
        
        // Return as download
        $tempFile = tempnam(sys_get_temp_dir(), 'count_template');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error generating count template: ' . $e->getMessage(), [
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error generating template: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Import count data from uploaded file
 */
public function importCountData(Request $request)
{
    try {
        $request->validate([
            'import_date' => 'required|date',
            'store_name' => 'nullable|string',
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // 10MB max
        ]);

        $role = Auth::user()->role;
        $userStoreId = Auth::user()->storeid;
        $importDate = $request->import_date;
        
        // Determine which store based on user role
        $storeName = null;
        if ($role === 'ADMIN' || $role === 'SUPERADMIN') {
            $storeName = $request->store_name;
            if (!$storeName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store name is required for admin users'
                ], 422);
            }
        } else {
            $storeRecord = rbostoretables::where('STOREID', $userStoreId)->first();
            $storeName = $storeRecord ? $storeRecord->NAME : $userStoreId;
        }

        Log::info('Starting count data import', [
            'import_date' => $importDate,
            'store_name' => $storeName,
            'user_id' => Auth::id(),
            'file_name' => $request->file('import_file')->getClientOriginalName()
        ]);

        DB::beginTransaction();

        // Check if count data already exists for this date and store
        $existingCountData = DB::table('stockcountingtrans')
            ->where('STORENAME', $storeName)
            ->whereDate('TRANSDATE', $importDate)
            ->where('COUNTED', '>', 0) // Check for actual count data, not just empty records
            ->count();

        if ($existingCountData > 0) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Count data already exists for {$storeName} on {$importDate}. Cannot import duplicate data. Please use a different date or delete existing data first."
            ], 409); // 409 Conflict status
        }

        // Read the uploaded file
        $file = $request->file('import_file');
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(\PhpOffice\PhpSpreadsheet\IOFactory::identify($file->path()));
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->path());
        $worksheet = $spreadsheet->getActiveSheet();
        
        $rows = $worksheet->toArray(null, true, true, true);
        
        if (empty($rows) || count($rows) < 2) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'File appears to be empty or has no data rows'
            ], 400);
        }
        
        // Skip header row and process data
        $headers = array_shift($rows);
        $totalImported = 0;
        $totalUpdated = 0;
        $errors = [];
        
        // Get existing records to update or create new ones
        $existingJournals = DB::table('stockcountingtables')
            ->where('STOREID', $storeName)
            ->whereDate('CREATEDDATETIME', $importDate)
            ->get()
            ->keyBy('JOURNALID');
        
        // Create journal if doesn't exist
        if ($existingJournals->isEmpty()) {
            $journalId = DB::table('stockcountingtables')->insertGetId([
                'DESCRIPTION' => 'Imported Count - ' . $importDate,
                'POSTED' => 0,
                'POSTEDDATETIME' => now(), // Set to current datetime instead of null
                'JOURNALTYPE' => 1, // Should be integer, not string
                'DELETEPOSTEDLINES' => 0,
                'CREATEDDATETIME' => $importDate,
                'STOREID' => $storeName,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $journalId = $existingJournals->first()->JOURNALID;
        }
        
        foreach ($rows as $rowIndex => $row) {
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                $itemId = trim($row['A'] ?? '');
                $itemName = trim($row['B'] ?? '');
                $itemDepartment = trim($row['C'] ?? '');
                $counted = floatval($row['F'] ?? 0);
                $wasteCount = floatval($row['G'] ?? 0);
                $wasteType = trim($row['H'] ?? '');
                $receivedCount = floatval($row['I'] ?? 0);
                $transferCount = floatval($row['J'] ?? 0);
                
                if (empty($itemId)) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": ITEMID is required";
                    continue;
                }
                
                // Check if record already exists
                $existingRecord = DB::table('stockcountingtrans')
                    ->where('JOURNALID', $journalId)
                    ->where('ITEMID', $itemId)
                    ->where('STORENAME', $storeName)
                    ->whereDate('TRANSDATE', $importDate)
                    ->first();
                
                $data = [
                    'JOURNALID' => $journalId,
                    'LINENUM' => $rowIndex + 1,
                    'TRANSDATE' => $importDate,
                    'ITEMID' => $itemId,
                    'ITEMDEPARTMENT' => $itemDepartment,
                    'STORENAME' => $storeName,
                    'COUNTED' => $counted,
                    'WASTECOUNT' => $wasteCount,
                    'WASTETYPE' => $wasteType,
                    'RECEIVEDCOUNT' => $receivedCount,
                    'TRANSFERCOUNT' => $transferCount,
                    'POSTED' => 0,
                    'POSTEDDATETIME' => now(), // Set to current datetime instead of null
                    'updated_at' => now()
                ];
                
                if ($existingRecord) {
                    // Update existing record
                    DB::table('stockcountingtrans')
                        ->where('id', $existingRecord->id)
                        ->update($data);
                    $totalUpdated++;
                } else {
                    // Create new record
                    $data['created_at'] = now();
                    DB::table('stockcountingtrans')->insert($data);
                    $totalImported++;
                }
                
            } catch (\Exception $rowError) {
                $errors[] = "Row " . ($rowIndex + 2) . ": " . $rowError->getMessage();
                Log::warning('Error processing row in import', [
                    'row_index' => $rowIndex + 2,
                    'error' => $rowError->getMessage(),
                    'row_data' => $row
                ]);
            }
        }
        
        if (!empty($errors) && ($totalImported + $totalUpdated) === 0) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed with errors',
                'errors' => $errors
            ], 400);
        }
        
        // Log the import operation
        if (Schema::hasTable('import_logs')) {
            DB::table('import_logs')->insert([
                'import_type' => 'count_data',
                'import_date' => $importDate,
                'store_name' => $storeName,
                'user_id' => Auth::id(),
                'file_name' => $request->file('import_file')->getClientOriginalName(),
                'total_rows' => count($rows),
                'imported_records' => $totalImported,
                'updated_records' => $totalUpdated,
                'errors' => json_encode($errors),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        DB::commit();

        Log::info('Count data import completed', [
            'import_date' => $importDate,
            'store_name' => $storeName,
            'total_imported' => $totalImported,
            'total_updated' => $totalUpdated,
            'errors_count' => count($errors)
        ]);

        // Generate inventory summaries after successful import
        $summaryGenerated = false;
        try {
            Log::info('Generating inventory summary after import', [
                'import_date' => $importDate,
                'store_name' => $storeName
            ]);
            
            // Get store ID from store name for the command
            $storeId = DB::table('rbostoretables')
                ->where('NAME', $storeName)
                ->value('STOREID');
            
            if ($storeId) {
                // Run the inventory summary generation command
                \Artisan::call('inventory:generate-summary', [
                    'date' => $importDate,
                    '--store' => $storeId,
                    '--force' => true
                ]);
                
                $summaryGenerated = true;
                Log::info('Inventory summary generated successfully after import');
            } else {
                Log::warning('Could not find store ID for store name: ' . $storeName);
            }
        } catch (\Exception $summaryError) {
            Log::error('Failed to generate inventory summary after import: ' . $summaryError->getMessage(), [
                'import_date' => $importDate,
                'store_name' => $storeName,
                'error' => $summaryError->getTraceAsString()
            ]);
        }

        $message = "Import completed successfully for {$storeName} on {$importDate}";
        if ($summaryGenerated) {
            $message .= "\nInventory summaries have been generated and data should now appear in the variance report.";
        } else {
            $message .= "\nNote: Please run inventory summary generation to see data in the variance report.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'import_date' => $importDate,
                'store_name' => $storeName,
                'total_imported' => $totalImported,
                'total_updated' => $totalUpdated,
                'total_processed' => $totalImported + $totalUpdated,
                'errors' => $errors,
                'summary_generated' => $summaryGenerated
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error importing count data: ' . $e->getMessage(), [
            'request_data' => $request->except('import_file'),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error importing data: ' . $e->getMessage()
        ], 500);
    }
}
}