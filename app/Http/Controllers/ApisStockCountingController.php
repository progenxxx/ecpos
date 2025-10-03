<?php

namespace App\Http\Controllers;

use App\Models\stockcountingtables;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
            Log::info('No unposted stock counting records found, creating new record', ['storeids' => $storeids]);

            // Create new record
            DB::beginTransaction();
            try {
                $currentDateTime = Carbon::now('Asia/Manila');

                $stocknextrec = DB::table('nubersequencevalues')
                    ->where('storeid', $storeids)
                    ->lockForUpdate()
                    ->value('stocknextrec');

                $stocknextrec = $stocknextrec !== null ? (int)$stocknextrec + 1 : 1;

                DB::table('nubersequencevalues')
                    ->where('STOREID', $storeids)
                    ->update(['stocknextrec' => $stocknextrec]);

                $journalId = $storeids . str_pad($stocknextrec, 8, '0', STR_PAD_LEFT);

                // Check if journalid already exists and increment if necessary
                $finalJournalId = $stocknextrec;
                while (DB::table('stockcountingtables')
                    ->where('JOURNALID', $finalJournalId)
                    ->exists()) {
                    $finalJournalId++;
                    Log::info('Journalid exists, incrementing', [
                        'original' => $stocknextrec,
                        'new' => $finalJournalId
                    ]);
                }

                // Update the journalId for description
                $journalId = $storeids . str_pad($finalJournalId, 8, '0', STR_PAD_LEFT);

                DB::table('stockcountingtables')->insert([
                    'JOURNALID' => $finalJournalId,
                    'STOREID' => $storeids,
                    'DESCRIPTION' => 'BATCH' . $journalId,
                    'POSTED' => 0,
                    'POSTEDDATETIME' => $currentDateTime->format('Y-m-d H:i:s'),
                    'JOURNALTYPE' => 1,
                    'DELETEPOSTEDLINES' => 0,
                    'CREATEDDATETIME' => $currentDateTime->format('Y-m-d H:i:s')
                ]);

                // Check if inventory summaries exist for this store and date
                $existingSummaries = DB::table('inventory_summaries')
                    ->where('storename', $storeids)
                    ->whereDate('report_date', $currentDate)
                    ->exists();

                // If no existing summaries, insert new ones
                if (!$existingSummaries) {
                    DB::table('inventory_summaries')
                        ->insertUsing(
                            ['itemid', 'itemname', 'storename', 'report_date', 'sync'],
                            DB::table('inventtables')
                                ->select('itemid', 'itemname', DB::raw("'{$storeids}' as storename"), DB::raw("'{$currentDate}' as report_date"), DB::raw('0 as sync'))
                        );
                }

                DB::commit();

                // Re-run the query to get the newly created record
                $stockCounting = $query
                    ->groupBy('a.journalid', 'a.storeid', 'a.description', 'a.posted', 
                             'a.updated_at', 'a.journaltype', 'a.createddatetime')
                    ->orderBy('a.createddatetime', 'DESC')
                    ->get();

                Log::info('New stock counting record created', [
                    'journalid' => $finalJournalId,
                    'storeids' => $storeids
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Detailed error in transaction: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                throw $e;
            }
        } else {
            // Check and insert missing items for both stockcountingtrans and inventory_summaries
            Log::info('Stock counting records found, checking for missing items', ['storeids' => $storeids]);
            
            foreach ($stockCounting as $stockCountRecord) {
                $this->insertMissingItemsToStockCountingTrans($stockCountRecord->journalid, $storeids, $currentDate);
                $this->insertMissingItemsToInventorySummaries($storeids, $currentDate);
            }

            // Check for all unsynced inventory_summaries records
            $unsyncedRecords = DB::table('inventory_summaries')
                ->select('storename', DB::raw('DATE(report_date) as report_date'))
                ->where('sync', 0)
                ->groupBy('storename', DB::raw('DATE(report_date)'))
                ->get();

            if ($unsyncedRecords->count() > 0) {
                Log::info('Found unsynced inventory summaries', [
                    'count' => $unsyncedRecords->count()
                ]);

                foreach ($unsyncedRecords as $record) {
                    try {
                        $reportDate = $record->report_date;
                        $storeName = $record->storename;

                        // Calculate yesterday based on the report_date
                        $yesterday = Carbon::parse($reportDate)->subDay()->toDateString();

                        Log::info('Processing unsynced inventory summary', [
                            'storename' => $storeName,
                            'report_date' => $reportDate,
                            'yesterday' => $yesterday
                        ]);

                        $this->updateInventorySummaries($storeName, $reportDate, $yesterday);

                        // Mark as synced
                        DB::table('inventory_summaries')
                            ->where('storename', $storeName)
                            ->whereDate('report_date', $reportDate)
                            ->update(['sync' => 1]);

                        Log::info('Inventory summaries synced', [
                            'storename' => $storeName,
                            'date' => $reportDate
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error syncing inventory summary', [
                            'storename' => $record->storename,
                            'report_date' => $record->report_date,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Continue with next record even if one fails
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $stockCounting
        ]);

    } catch (Exception $e) {
        Log::error('Error in stock counting index', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'sql' => DB::getQueryLog()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error loading stock counting data: ' . $e->getMessage()
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

        // Check if stockcountingtrans has any records for this journal
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

        // Get items that should be in stockcountingtrans but are missing
        if ($storeId == 'COMMUNITY') {
            // For COMMUNITY store, get items where activeondelivery = 1
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('stockcountingtrans as st', function($join) use ($journalId, $storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'st.ITEMID')
                         ->where('st.JOURNALID', '=', $journalId)
                         ->where('st.STORENAME', '=', $storeId)
                         ->whereDate('st.TRANSDATE', '=', $currentDate);
                })
                ->whereNull('st.ITEMID') // Items not in stockcountingtrans
                ->where('b.activeondelivery', '1')
                ->select('a.itemid', 'b.itemdepartment')
                ->get();
        } else {
            // For other stores, get items where itemname not like '%CLASS B%'
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('stockcountingtrans as st', function($join) use ($journalId, $storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'st.ITEMID')
                         ->where('st.JOURNALID', '=', $journalId)
                         ->where('st.STORENAME', '=', $storeId)
                         ->whereDate('st.TRANSDATE', '=', $currentDate);
                })
                ->whereNull('st.ITEMID') // Items not in stockcountingtrans
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

            // Prepare data for bulk insert
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

            // Insert missing items in chunks to avoid memory issues
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
        // Don't throw the exception to avoid breaking the main flow
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

        // Check if inventory_summaries has any records for this store and date
        $existingRecordsCount = DB::table('inventory_summaries')
            ->where('storename', $storeId)
            ->whereDate('report_date', $currentDate)
            ->count();

        if ($existingRecordsCount === 0) {
            Log::info('No existing inventory_summaries records found, skipping missing items check');
            return;
        }

        Log::info('Existing inventory_summaries records found', ['count' => $existingRecordsCount]);

        // Get items that should be in inventory_summaries but are missing
        if ($storeId == 'COMMUNITY') {
            // For COMMUNITY store, get items where activeondelivery = 1
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('inventory_summaries as inv', function($join) use ($storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'inv.itemid')
                         ->where('inv.storename', '=', $storeId)
                         ->whereDate('inv.report_date', '=', $currentDate);
                })
                ->whereNull('inv.itemid') // Items not in inventory_summaries
                ->where('b.activeondelivery', '1')
                ->select('a.itemid', 'a.itemname')
                ->get();
        } else {
            // For other stores, get items where itemname not like '%CLASS B%'
            $missingItems = DB::table('inventtables as a')
                ->leftJoin('rboinventtables as b', 'a.itemid', '=', 'b.itemid')
                ->leftJoin('inventory_summaries as inv', function($join) use ($storeId, $currentDate) {
                    $join->on('a.itemid', '=', 'inv.itemid')
                         ->where('inv.storename', '=', $storeId)
                         ->whereDate('inv.report_date', '=', $currentDate);
                })
                ->whereNull('inv.itemid') // Items not in inventory_summaries
                ->where('a.itemname', 'not like', '%CLASS B%')
                ->select('a.itemid', 'a.itemname')
                ->get();
        }

        if ($missingItems->count() > 0) {
            Log::info('Missing items found, inserting into inventory_summaries', [
                'count' => $missingItems->count(),
                'storeId' => $storeId
            ]);

            // Prepare data for bulk insert
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

            // Insert missing items in chunks to avoid memory issues
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
        // Don't throw the exception to avoid breaking the main flow
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

            // Let's try direct update first
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

            // Fetch the updated record
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

    private function updateInventorySummaries($storename, $currentDateTime, $yesterday)
    {
        Log::info("Starting inventory summaries update", [
            'storename' => $storename,
            'current_date' => $currentDateTime,
            'yesterday' => $yesterday
        ]);

        try {
            // First, insert missing items from stockcountingtrans into inventory_summaries
            Log::info("Checking for missing items in inventory_summaries");

            $missingItems = DB::table('stockcountingtrans as st')
                ->leftJoin('inventory_summaries as inv', function($join) use ($storename, $currentDateTime) {
                    $join->on('st.ITEMID', '=', 'inv.itemid')
                         ->where('inv.storename', '=', $storename)
                         ->whereDate('inv.report_date', '=', $currentDateTime);
                })
                ->leftJoin('inventtables as it', 'st.ITEMID', '=', 'it.itemid')
                ->whereNull('inv.itemid')
                ->where('st.STORENAME', $storename)
                ->whereDate('st.TRANSDATE', $currentDateTime)
                ->select('st.ITEMID', 'it.itemname')
                ->distinct()
                ->get();

            if ($missingItems->count() > 0) {
                Log::info("Found missing items, inserting into inventory_summaries", [
                    'count' => $missingItems->count()
                ]);

                $insertData = [];
                foreach ($missingItems as $item) {
                    $insertData[] = [
                        'itemid' => $item->ITEMID,
                        'itemname' => $item->itemname ?? $item->ITEMID,
                        'storename' => $storename,
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
                        'report_date' => $currentDateTime,
                        'sync' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                // Insert in chunks
                $chunks = array_chunk($insertData, 100);
                foreach ($chunks as $chunk) {
                    DB::table('inventory_summaries')->insert($chunk);
                }

                Log::info("Missing items inserted successfully", [
                    'count' => count($insertData)
                ]);
            }

            // Update throw_away
            Log::info("Updating throw_away for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET throw_away = (
                    SELECT SUM(WASTECOUNT)
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                      AND WASTETYPE = 'Throw Away'
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
                  AND EXISTS (
                    SELECT 1
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                      AND WASTETYPE = 'Throw Away'
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
            ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);

            Log::info("Throw away update completed", ['affected_rows' => $affectedRows]);


            // Update early_molds
            Log::info("Updating early_molds for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET early_molds = (
                    SELECT SUM(WASTECOUNT)
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                      AND WASTETYPE = 'Early Molds'
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
                  AND EXISTS (
                    SELECT 1
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                      AND WASTETYPE = 'Early Molds'
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
            ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);

            Log::info("Early Molds update completed", ['affected_rows' => $affectedRows]);


            // Update pull_out
            Log::info("Updating pull_out for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET pull_out = (
                    SELECT SUM(WASTECOUNT)
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                      AND WASTETYPE = 'Pull Out'
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
                  AND EXISTS (
                    SELECT 1
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                      AND WASTETYPE = 'Pull Out'
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
            ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);

            Log::info("Pull Out update completed", ['affected_rows' => $affectedRows]);


            // Update rat_bites
            Log::info("Updating rat_bites for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET rat_bites = (
                    SELECT SUM(WASTECOUNT)
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                      AND WASTETYPE = 'Rat Bites'
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
                  AND EXISTS (
                    SELECT 1
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                      AND WASTETYPE = 'Rat Bites'
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
            ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);

            Log::info("Rat Bites update completed", ['affected_rows' => $affectedRows]);


            // Update ant_bites
            Log::info("Updating ant_bites for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET ant_bites = (
                    SELECT SUM(WASTECOUNT)
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                      AND WASTETYPE = 'Ant Bites'
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
                  AND EXISTS (
                    SELECT 1
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                      AND WASTETYPE = 'Ant Bites'
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
            ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);

            Log::info("Ant Bites update completed", ['affected_rows' => $affectedRows]);


            // Update received_delivery
            Log::info("Updating received_delivery for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET received_delivery = (
                    SELECT SUM(RECEIVEDCOUNT)
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
                  AND EXISTS (
                    SELECT 1
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                )
            ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);

            Log::info("Received delivery update completed", ['affected_rows' => $affectedRows]);

            // Update sales
            Log::info("Updating sales for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET sales = (
                    SELECT COALESCE(SUM(b.qty), 0)
                    FROM rbotransactiontables a
                    LEFT JOIN rbotransactionsalestrans b ON a.transactionid = b.transactionid
                    LEFT JOIN rboinventtables c ON b.itemid = c.itemid
                    WHERE a.store = ?
                    AND CAST(a.createddate AS DATE) = ?
                    AND b.itemgroup NOT LIKE '%PROMO%'
                    AND c.itemid = inventory_summaries.itemid
                )
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
                  AND inventory_summaries.itemid IN (
                    SELECT DISTINCT c.itemid
                    FROM rbotransactiontables a
                    LEFT JOIN rbotransactionsalestrans b ON a.transactionid = b.transactionid
                    LEFT JOIN rboinventtables c ON b.itemid = c.itemid
                    WHERE a.store = ?
                    AND CAST(a.createddate AS DATE) = ?
                    AND b.itemgroup NOT LIKE '%PROMO%'
                    AND c.itemid IS NOT NULL
                )
            ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);

            Log::info("Sales update completed", ['affected_rows' => $affectedRows]);

            // Update bundle_sales
            Log::info("Updating bundle_sales for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries invs
                JOIN (
                    SELECT
                        il.child_itemid,
                        il.quantity AS link_quantity,
                        COALESCE(SUM(rst.qty), 0) * il.quantity AS result
                    FROM item_links il
                    LEFT JOIN rbotransactionsalestrans rst
                        ON rst.itemid = il.parent_itemid OR rst.itemid = il.child_itemid
                    WHERE
                        CAST(rst.createddate AS DATE) = ?
                        AND rst.store = ?
                        AND rst.itemgroup LIKE '%PROMO%'
                    GROUP BY il.child_itemid, il.quantity
                ) AS bundle_data
                ON invs.itemid = bundle_data.child_itemid
                   AND invs.storename = ?
                SET invs.bundle_sales = bundle_data.result
                WHERE CAST(invs.report_date AS DATE) = ?
            ", [$currentDateTime, $storename, $storename, $currentDateTime]);

            Log::info("Bundle sales update completed", ['affected_rows' => $affectedRows]);

            // Update beginning
            Log::info("Updating beginning inventory for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET beginning = (
                    SELECT item_count
                    FROM inventory_summaries prev
                    WHERE prev.ITEMID = inventory_summaries.ITEMID
                      AND prev.STORENAME = ?
                      AND CAST(prev.report_date AS DATE) = ?
                )
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
                  AND EXISTS (
                    SELECT 1
                    FROM inventory_summaries prev
                    WHERE prev.ITEMID = inventory_summaries.ITEMID
                      AND prev.STORENAME = ?
                      AND CAST(prev.report_date AS DATE) = ?
                )
            ", [$storename, $yesterday, $currentDateTime, $storename, $storename, $yesterday]);

            Log::info("Beginning inventory update completed", ['affected_rows' => $affectedRows]);

            // Update ending inventory
            Log::info("Updating ending inventory for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET ending = CASE
                    WHEN beginning IS NOT NULL
                         AND beginning != 0
                         AND COALESCE(received_delivery, 0) = 0
                         AND COALESCE(stock_transfer, 0) = 0
                         AND COALESCE(sales, 0) = 0
                         AND COALESCE(bundle_sales, 0) = 0
                         AND COALESCE(throw_away, 0) = 0
                         AND COALESCE(early_molds, 0) = 0
                         AND COALESCE(pull_out, 0) = 0
                         AND COALESCE(rat_bites, 0) = 0
                         AND COALESCE(ant_bites, 0) = 0
                    THEN beginning
                    ELSE COALESCE(beginning, 0) + COALESCE(received_delivery, 0) - COALESCE(stock_transfer, 0) - COALESCE(sales, 0) - COALESCE(bundle_sales, 0) - COALESCE(throw_away, 0) - COALESCE(early_molds, 0) - COALESCE(pull_out, 0) - COALESCE(rat_bites, 0) - COALESCE(ant_bites, 0)
                END
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
            ", [$currentDateTime, $storename]);

            Log::info("Ending inventory update completed", ['affected_rows' => $affectedRows]);

            // Update item_count with calculated ending inventory
            Log::info("Updating item_count with calculated ending inventory");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET item_count = CASE
                    WHEN beginning IS NOT NULL
                         AND beginning != 0
                         AND COALESCE(received_delivery, 0) = 0
                         AND COALESCE(stock_transfer, 0) = 0
                         AND COALESCE(sales, 0) = 0
                         AND COALESCE(bundle_sales, 0) = 0
                         AND COALESCE(throw_away, 0) = 0
                         AND COALESCE(early_molds, 0) = 0
                         AND COALESCE(pull_out, 0) = 0
                         AND COALESCE(rat_bites, 0) = 0
                         AND COALESCE(ant_bites, 0) = 0
                    THEN beginning
                    ELSE 0
                END
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
            ", [$currentDateTime, $storename]);

            Log::info("Item count recalculation completed", ['affected_rows' => $affectedRows]);

            // Update item_count
            Log::info("Updating item_count for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET item_count = (
                    SELECT COUNTED
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                      AND COUNTED > 0
                )
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
                  AND EXISTS (
                    SELECT 1
                    FROM stockcountingtrans
                    WHERE stockcountingtrans.itemid = inventory_summaries.itemid
                      AND STORENAME = ?
                      AND CAST(TRANSDATE AS DATE) = ?
                      AND COUNTED > 0
                )
            ", [$storename, $currentDateTime, $currentDateTime, $storename, $storename, $currentDateTime]);

            Log::info("Item count update completed", ['affected_rows' => $affectedRows]);

            // Final item_count update with null safety
            Log::info("Performing final item_count update with null safety");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET item_count = CASE
                    WHEN beginning IS NOT NULL
                         AND beginning != 0
                         AND COALESCE(received_delivery, 0) = 0
                         AND COALESCE(stock_transfer, 0) = 0
                         AND COALESCE(sales, 0) = 0
                         AND COALESCE(bundle_sales, 0) = 0
                         AND COALESCE(throw_away, 0) = 0
                         AND COALESCE(early_molds, 0) = 0
                         AND COALESCE(pull_out, 0) = 0
                         AND COALESCE(rat_bites, 0) = 0
                         AND COALESCE(ant_bites, 0) = 0
                    THEN beginning
                    ELSE item_count
                END
                WHERE CAST(report_date AS DATE) = ?
                AND storename = ?
            ", [$currentDateTime, $storename]);

            Log::info("Final item count update completed", ['affected_rows' => $affectedRows]);

            // Update variance
            Log::info("Updating variance for inventory summaries");
            $affectedRows = DB::statement("
                UPDATE inventory_summaries
                SET variance = COALESCE(ending, 0) - COALESCE(item_count, 0)
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
            ", [$currentDateTime, $storename]);

            Log::info("Variance update completed", ['affected_rows' => $affectedRows]);

            // Log summary of what was updated
            $summaryData = DB::select("
                SELECT
                    COUNT(*) as total_records,
                    SUM(CASE WHEN item_count IS NOT NULL THEN 1 ELSE 0 END) as records_with_count,
                    SUM(CASE WHEN variance != 0 THEN 1 ELSE 0 END) as records_with_variance,
                    SUM(CASE WHEN ending IS NOT NULL THEN 1 ELSE 0 END) as records_with_ending
                FROM inventory_summaries
                WHERE CAST(report_date AS DATE) = ? AND storename = ?
            ", [$currentDateTime, $storename]);

            Log::info("Inventory summaries update completed successfully", [
                'summary' => $summaryData[0] ?? null,
                'storename' => $storename,
                'date' => $currentDateTime
            ]);

        } catch (\Exception $e) {
            Log::error("Error updating inventory summaries", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'storename' => $storename,
                'current_date' => $currentDateTime,
                'yesterday' => $yesterday
            ]);
            throw $e;
        }
    }
}