<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncInventorySummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:sync {--store= : Sync only specific store} {--date= : Sync only specific date} {--limit=10 : Number of records to process per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync unsynced inventory summaries records';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting inventory summaries sync...');

        $store = $this->option('store');
        $date = $this->option('date');
        $limit = (int) $this->option('limit');

        // Build query for unsynced records
        $query = DB::table('inventory_summaries')
            ->select('storename', DB::raw('DATE(report_date) as report_date'))
            ->where('sync', 0)
            ->groupBy('storename', DB::raw('DATE(report_date)'))
            ->orderBy('report_date', 'ASC');

        if ($store) {
            $query->where('storename', $store);
        }

        if ($date) {
            $query->whereDate('report_date', $date);
        }

        $unsyncedRecords = $query->limit($limit)->get();

        if ($unsyncedRecords->count() === 0) {
            $this->info('No unsynced records found.');
            return 0;
        }

        $this->info("Found {$unsyncedRecords->count()} unsynced records to process.");

        $successCount = 0;
        $errorCount = 0;

        foreach ($unsyncedRecords as $record) {
            try {
                $reportDate = $record->report_date;
                $storeName = $record->storename;
                $yesterday = Carbon::parse($reportDate)->subDay()->toDateString();

                $this->info("Processing: {$storeName} - {$reportDate}");

                $this->updateInventorySummaries($storeName, $reportDate, $yesterday);

                // Mark as synced
                DB::table('inventory_summaries')
                    ->where('storename', $storeName)
                    ->whereDate('report_date', $reportDate)
                    ->update(['sync' => 1]);

                $this->info("✓ Synced: {$storeName} - {$reportDate}");
                $successCount++;

            } catch (\Exception $e) {
                $this->error("✗ Error syncing {$record->storename} - {$record->report_date}: {$e->getMessage()}");
                Log::error('Error syncing inventory summary in command', [
                    'storename' => $record->storename,
                    'report_date' => $record->report_date,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $errorCount++;
            }
        }

        $this->info("\nSync completed!");
        $this->info("Success: {$successCount}");
        $this->info("Errors: {$errorCount}");

        return 0;
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

                $chunks = array_chunk($insertData, 100);
                foreach ($chunks as $chunk) {
                    DB::table('inventory_summaries')->insert($chunk);
                }
            }

            // Update throw_away
            DB::statement("
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

            // Update early_molds
            DB::statement("
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

            // Update pull_out
            DB::statement("
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

            // Update rat_bites
            DB::statement("
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

            // Update ant_bites
            DB::statement("
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

            // Update received_delivery
            DB::statement("
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

            // Update sales
            DB::statement("
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

            // Update bundle_sales
            DB::statement("
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

            // Update beginning
            DB::statement("
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

            // Update ending inventory
            DB::statement("
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

            // Update item_count with calculated ending inventory
            DB::statement("
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

            // Update item_count
            DB::statement("
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

            // Final item_count update with null safety
            DB::statement("
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

            // Update variance
            DB::statement("
                UPDATE inventory_summaries
                SET variance = COALESCE(ending, 0) - COALESCE(item_count, 0)
                WHERE CAST(report_date AS DATE) = ?
                  AND storename = ?
            ", [$currentDateTime, $storename]);

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
