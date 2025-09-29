<?php

namespace App\Http\Controllers;

use App\Models\stockcountingtables;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ApisStockCountingController extends Controller
{
    public function index(Request $request, $storeids)
{
    DB::enableQueryLog(); 

    try {
        $currentDate = Carbon::now('Asia/Manila')->toDateString();
        
        $query = DB::table('stockcountingtables AS a')
            ->select(
                'a.journalid',
                'a.storeid',
                'a.description',
                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS qty'),
                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2)) * CAST(b.COUNTED AS UNSIGNED)) AS amount'),
                'a.posted',
                'a.updated_at',
                'a.journaltype',
                'a.createddatetime'
            )
            ->leftJoin('stockcountingtrans AS b', 'b.JOURNALID', '=', 'a.journalid')
            ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
            ->where('a.posted', '!=', '1')
            ->whereDate('a.createddatetime', '=', $currentDate);

        if ($storeids !== "HQ2") {
            $query->where('a.storeid', '=', $storeids);
        }

        $stockCounting = $query
            ->groupBy('a.journalid', 'a.storeid', 'a.description', 'a.posted', 
                     'a.updated_at', 'a.journaltype', 'a.createddatetime')
            ->orderBy('a.createddatetime', 'DESC')
            ->get();

        if ($stockCounting->isEmpty()) {
            Log::info('No unposted stock counting records found, checking for posted records', ['storeids' => $storeids]);

            $postedRecordsQuery = DB::table('stockcountingtables')
                ->where('posted', '=', '1')
                ->whereDate('createddatetime', '=', $currentDate);

            if ($storeids !== "HQ2") {
                $postedRecordsQuery->where('storeid', '=', $storeids);
            }

            $hasPostedRecords = $postedRecordsQuery->exists();

            if ($hasPostedRecords) {
                Log::info('Posted stock counting records already exist for current date', [
                    'storeids' => $storeids,
                    'date' => $currentDate
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Stock counting already completed for today'
                ]);
            }

            DB::beginTransaction();
            try {
                $currentDateTime = Carbon::now('Asia/Manila');

                $stocknextrec = DB::table('nubersequencevalues')
                ->lockForUpdate()
                ->max('stocknextrec');

                $stocknextrec = $stocknextrec !== null ? (int)$stocknextrec + 1 : 1;

                DB::table('nubersequencevalues')
                    ->where('STOREID', $storeids)
                    ->update(['stocknextrec' => $stocknextrec]);

                $journalId = $storeids . str_pad($stocknextrec, 8, '0', STR_PAD_LEFT);

                DB::table('stockcountingtables')->insert([
                    'JOURNALID' => $stocknextrec,
                    'STOREID' => $storeids,
                    'DESCRIPTION' => 'BATCH' . $journalId,
                    'POSTED' => 0,
                    'POSTEDDATETIME' => $currentDateTime->format('Y-m-d H:i:s'),
                    'JOURNALTYPE' => 1,
                    'DELETEPOSTEDLINES' => 0,
                    'CREATEDDATETIME' => $currentDateTime->format('Y-m-d H:i:s')
                ]);

                $existingSummaries = DB::table('inventory_summaries')
                    ->where('storename', $storeids)
                    ->whereDate('report_date', $currentDate)
                    ->exists();

                if (!$existingSummaries) {
                    DB::table('inventory_summaries')
                        ->insertUsing(
                            ['itemid', 'itemname', 'storename', 'report_date'],
                            DB::table('inventtables')
                                ->select('itemid', 'itemname', DB::raw("'{$storeids}' as storename"), DB::raw("'{$currentDate}' as report_date"))
                        );
                }

                $this->updateInventorySummaries($storeids, $currentDate);

                DB::commit();

                $stockCounting = $query
                    ->groupBy('a.journalid', 'a.storeid', 'a.description', 'a.posted', 
                             'a.updated_at', 'a.journaltype', 'a.createddatetime')
                    ->orderBy('a.createddatetime', 'DESC')
                    ->get();

                Log::info('New stock counting record created', [
                    'journalid' => $stocknextrec,
                    'storeids' => $storeids
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Batch ID creation failed in ApisStockCountingController, attempting fallback to StockCountingController', [
                    'error' => $e->getMessage(),
                    'storeids' => $storeids
                ]);

                try {
                    $this->callStockCountingControllerStore($storeids);
                    Log::info('Fallback to StockCountingController store successful', ['storeids' => $storeids]);

                    // Re-query stock counting records after successful fallback
                    $stockCounting = $query
                        ->groupBy('a.journalid', 'a.storeid', 'a.description', 'a.posted',
                                 'a.updated_at', 'a.journaltype', 'a.createddatetime')
                        ->orderBy('a.createddatetime', 'DESC')
                        ->get();

                } catch (Exception $fallbackException) {
                    Log::error('Fallback to StockCountingController store also failed', [
                        'error' => $fallbackException->getMessage(),
                        'storeids' => $storeids
                    ]);
                    throw $e;
                }
            }
        } else {
            Log::info('Stock counting records found, checking for missing items', ['storeids' => $storeids]);
            
            foreach ($stockCounting as $stockCountRecord) {
                $this->insertMissingItemsToStockCountingTrans($stockCountRecord->journalid, $storeids, $currentDate);
                $this->insertMissingItemsToInventorySummaries($storeids, $currentDate);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $stockCounting
        ]);

    } catch (\Throwable $e) {
        Log::error('Error in stock counting index', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'sql' => DB::getQueryLog(),
            'storeids' => $storeids ?? 'not_set'
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error loading stock counting data: ' . $e->getMessage(),
            'error_details' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ], 500);
    }
}

/**
 * Insert missing items into stockcountingtrans table
 * 
 * @param int $journalId
 * @param string $storeId
 * @param string $currentDate
 * @return void
 */
private function insertMissingItemsToStockCountingTrans($journalId, $storeId, $currentDate)
{
    try {
        Log::info('Checking for missing items in stockcountingtrans', [
            'journalId' => $journalId,
            'storeId' => $storeId,
            'date' => $currentDate
        ]);

        $existingRecordsCount = DB::table('stockcountingtrans')
            ->where('JOURNALID', $journalId)
            ->where('STORENAME', $storeId)
            ->whereDate('TRANSDATE', $currentDate)
            ->count();

        if ($existingRecordsCount === 0) {
            Log::info('No existing stockcountingtrans records found, skipping missing items check');
            return;
        }

        Log::info('Existing stockcountingtrans records found', ['count' => $existingRecordsCount]);

        if ($storeId == 'COMMUNITY') {
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('stockcountingtrans as st', function($join) use ($journalId, $storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'st.ITEMID')
                         ->where('st.JOURNALID', '=', $journalId)
                         ->where('st.STORENAME', '=', $storeId)
                         ->whereDate('st.TRANSDATE', '=', $currentDate);
                })
                ->whereNull('st.ITEMID') 
                ->where('b.activeondelivery', '1')
                ->select('a.itemid', 'b.itemdepartment')
                ->get();
        } else {
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('stockcountingtrans as st', function($join) use ($journalId, $storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'st.ITEMID')
                         ->where('st.JOURNALID', '=', $journalId)
                         ->where('st.STORENAME', '=', $storeId)
                         ->whereDate('st.TRANSDATE', '=', $currentDate);
                })
                ->whereNull('st.ITEMID') 
                ->where('a.itemname', 'not like', '%CLASS B%')
                ->select('a.itemid', 'b.itemdepartment')
                ->get();
        }

        if ($missingItems->count() > 0) {
            Log::info('Missing items found, inserting into stockcountingtrans', [
                'count' => $missingItems->count(),
                'journalId' => $journalId,
                'storeId' => $storeId
            ]);

            $insertData = [];
            foreach ($missingItems as $item) {
                $insertData[] = [
                    'JOURNALID' => $journalId,
                    'ITEMDEPARTMENT' => $item->itemdepartment,
                    'TRANSDATE' => $currentDate,
                    'ITEMID' => $item->itemid,
                    'ADJUSTMENT' => 0,
                    'RECEIVEDCOUNT' => 0,
                    'WASTECOUNT' => 0,
                    'COUNTED' => 0,
                    'STORENAME' => $storeId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            $chunks = array_chunk($insertData, 100);
            foreach ($chunks as $chunk) {
                DB::table('stockcountingtrans')->insert($chunk);
            }

            Log::info('Missing items successfully inserted into stockcountingtrans', [
                'inserted_count' => count($insertData),
                'journalId' => $journalId,
                'storeId' => $storeId
            ]);
        } else {
            Log::info('No missing items found for stockcountingtrans', [
                'journalId' => $journalId,
                'storeId' => $storeId
            ]);
        }

    } catch (\Exception $e) {
        Log::error('Error inserting missing items to stockcountingtrans', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'journalId' => $journalId,
            'storeId' => $storeId
        ]);
    }
}

/**
 * Insert missing items into inventory_summaries table
 * 
 * @param string $storeId
 * @param string $currentDate
 * @return void
 */
private function insertMissingItemsToInventorySummaries($storeId, $currentDate)
{
    try {
        Log::info('Checking for missing items in inventory_summaries', [
            'storeId' => $storeId,
            'date' => $currentDate
        ]);

        $existingRecordsCount = DB::table('inventory_summaries')
            ->where('storename', $storeId)
            ->whereDate('report_date', $currentDate)
            ->count();

        if ($existingRecordsCount === 0) {
            Log::info('No existing inventory_summaries records found, skipping missing items check');
            return;
        }

        Log::info('Existing inventory_summaries records found', ['count' => $existingRecordsCount]);

        if ($storeId == 'COMMUNITY') {
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('inventory_summaries as inv', function($join) use ($storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'inv.itemid')
                         ->where('inv.storename', '=', $storeId)
                         ->whereDate('inv.report_date', '=', $currentDate);
                })
                ->whereNull('inv.itemid') 
                ->where('b.activeondelivery', '1')
                ->select('a.itemid', 'a.itemname')
                ->get();
        } else {
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('inventory_summaries as inv', function($join) use ($storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'inv.itemid')
                         ->where('inv.storename', '=', $storeId)
                         ->whereDate('inv.report_date', '=', $currentDate);
                })
                ->whereNull('inv.itemid') 
                ->where('a.itemname', 'not like', '%CLASS B%')
                ->select('a.itemid', 'a.itemname')
                ->get();
        }

        if ($missingItems->count() > 0) {
            Log::info('Missing items found, inserting into inventory_summaries', [
                'count' => $missingItems->count(),
                'storeId' => $storeId
            ]);

            $insertData = [];
            foreach ($missingItems as $item) {
                $insertData[] = [
                    'itemid' => $item->itemid,
                    'itemname' => $item->itemname,
                    'storename' => $storeId,
                    'beginning' => 0.00,
                    'received_delivery' => 0.00,
                    'stock_transfer' => 0.00,
                    'sales' => 0.00,
                    'bundle_sales' => 0.00,
                    'throw_away' => 0.00,
                    'early_molds' => 0.00,
                    'pull_out' => 0.00,
                    'rat_bites' => 0.00,
                    'ant_bites' => 0.00,
                    'item_count' => 0.00,
                    'ending' => 0.00,
                    'variance' => 0.00,
                    'report_date' => $currentDate,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            $chunks = array_chunk($insertData, 100);
            foreach ($chunks as $chunk) {
                DB::table('inventory_summaries')->insert($chunk);
            }

            Log::info('Missing items successfully inserted into inventory_summaries', [
                'inserted_count' => count($insertData),
                'storeId' => $storeId
            ]);
        } else {
            Log::info('No missing items found for inventory_summaries', [
                'storeId' => $storeId
            ]);
        }

    } catch (\Exception $e) {
        Log::error('Error inserting missing items to inventory_summaries', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'storeId' => $storeId
        ]);
    }
}

    public function update(Request $request, $storeids, $posted, $journalid)
    {
        try {
            \Log::info('Starting stock counting update', [
                'storeids' => $storeids,
                'posted' => $posted,
                'journalid' => $journalid
            ]);
            
            $affected = DB::table('stockcountingtables')
                ->where('JOURNALID', $journalid)
                ->where('STOREID', $storeids)
                ->update([
                    'POSTED' => $posted,
                    'updated_at' => now()
                ]);
                
            if ($affected === 0) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }
            
            $stockCounting = stockcountingtables::where('JOURNALID', $journalid)
                ->where('STOREID', $storeids)
                ->first();
            
            \Log::info('Stock counting record updated', [
                'journalid' => $journalid,
                'posted' => $posted,
                'affected_rows' => $affected
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Stock counting record updated successfully',
                'data' => $stockCounting
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Stock counting record not found', [
                'journalid' => $journalid,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Stock counting record not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error updating stock counting record', [
                'journalid' => $journalid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the record'
            ], 500);
        }
    }

    /**
     * Update inventory summaries using optimized temporary table approach
     */
    private function updateInventorySummaries($storeName, $syncDate)
    {
        try {
            Log::info('Starting inventory summaries update', [
                'store_name' => $storeName,
                'sync_date' => $syncDate
            ]);

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

            $endingResult = DB::update("
                UPDATE inventory_summaries 
                SET ending = (beginning + received_delivery + COALESCE(stock_transfer, 0)) 
                           - (sales + bundle_sales + throw_away + early_molds + pull_out + rat_bites + ant_bites),
                    updated_at = CURRENT_TIMESTAMP
                WHERE storename = ?
                  AND CAST(report_date AS DATE) = ?
            ", [$storeName, $syncDate]);

            $varianceResult = DB::update("
                UPDATE inventory_summaries 
                SET variance = item_count - ending,
                    updated_at = CURRENT_TIMESTAMP
                WHERE storename = ?
                  AND CAST(report_date AS DATE) = ?
            ", [$storeName, $syncDate]);

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

            $totalAffectedRows = $beginningResult + $receivedResult + $salesResult + $bundleSalesResult + 
                               $throwAwayResult + $pullOutResult + $ratBitesResult + $antBitesResult + 
                               $earlyMoldsResult + $staffCountResult + $endingResult + $varianceResult;

            Log::info('Inventory summaries update completed', [
                'store_name' => $storeName,
                'sync_date' => $syncDate,
                'total_affected_rows' => $totalAffectedRows,
                'details' => [
                    'beginning' => $beginningResult,
                    'received' => $receivedResult,
                    'sales' => $salesResult,
                    'bundle_sales' => $bundleSalesResult,
                    'waste_types' => [
                        'throw_away' => $throwAwayResult,
                        'pull_out' => $pullOutResult,
                        'rat_bites' => $ratBitesResult,
                        'ant_bites' => $antBitesResult,
                        'early_molds' => $earlyMoldsResult
                    ],
                    'staff_count' => $staffCountResult,
                    'ending' => $endingResult,
                    'variance' => $varianceResult
                ]
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error updating inventory summaries in ApisStockCountingController', [
                'store_name' => $storeName,
                'sync_date' => $syncDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Fallback method using StockCountingController logic for stockcountingtables creation
     * Called when the primary batch ID creation fails
     */
    private function callStockCountingControllerStore($storeids)
    {
        DB::beginTransaction();

        try {
            $currentDateTime = Carbon::now('Asia/Manila');
            $userId = 1; // Default user ID for API calls

            // Check if stock counting already exists for today (StockCountingController logic)
            $existingOrder = DB::table('stockcountingtables')
                ->whereDate('CREATEDDATETIME', $currentDateTime)
                ->where('STOREID', $storeids)
                ->exists();

            if ($existingOrder) {
                throw new Exception('You have already Stock Counting this time.');
            }

            // Get and increment stocknextrec (StockCountingController logic)
            $stocknextrec = DB::table('nubersequencevalues')
                ->where('storeid', $storeids)
                ->lockForUpdate()
                ->value('stocknextrec');

            $stocknextrec = $stocknextrec !== null ? (int)$stocknextrec + 1 : 1;

            DB::table('nubersequencevalues')
                ->where('STOREID', $storeids)
                ->update(['stocknextrec' => $stocknextrec]);

            // Create journal ID and description (StockCountingController logic)
            $journalId = $userId . str_pad($stocknextrec, 8, '0', STR_PAD_LEFT);
            $description = "BATCH" . $journalId;

            // Insert into stockcountingtables (StockCountingController logic)
            DB::table('stockcountingtables')->insert([
                'JOURNALID' => $journalId,
                'STOREID' => $storeids,
                'DESCRIPTION' => $description,
                'POSTED' => "0",
                'POSTEDDATETIME' => $currentDateTime->format('Y-m-d H:i:s'),
                'JOURNALTYPE' => "1",
                'DELETEPOSTEDLINES' => "0",
                'CREATEDDATETIME' => $currentDateTime->format('Y-m-d H:i:s'),
            ]);

            // Add inventory summaries creation like in the original logic
            $currentDate = $currentDateTime->toDateString();

            $existingSummaries = DB::table('inventory_summaries')
                ->where('storename', $storeids)
                ->whereDate('report_date', $currentDate)
                ->exists();

            if (!$existingSummaries) {
                DB::table('inventory_summaries')
                    ->insertUsing(
                        ['itemid', 'itemname', 'storename', 'report_date'],
                        DB::table('inventtables')
                            ->select('itemid', 'itemname', DB::raw("'{$storeids}' as storename"), DB::raw("'{$currentDate}' as report_date"))
                    );
            }

            $this->updateInventorySummaries($storeids, $currentDate);

            DB::commit();

            Log::info('Fallback using StockCountingController logic successful', [
                'journalid' => $journalId,
                'storeids' => $storeids,
                'description' => $description
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Fallback using StockCountingController logic failed', [
                'error' => $e->getMessage(),
                'storeids' => $storeids,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}