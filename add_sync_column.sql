-- Add sync column to inventory_summaries table
-- This script adds a 'sync' column to track whether inventory summaries have been synchronized

-- Check if the column doesn't exist before adding it
ALTER TABLE `inventory_summaries`
ADD COLUMN IF NOT EXISTS `sync` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Sync status: 0 = not synced, 1 = synced';

-- Set existing records to sync = 0 (unsynced) so they will be processed
UPDATE `inventory_summaries`
SET `sync` = 0
WHERE `sync` IS NULL;

-- Add index on sync column for better query performance
CREATE INDEX IF NOT EXISTS `idx_sync` ON `inventory_summaries` (`sync`);

-- Add composite index for faster lookup of unsynced records by store and date
CREATE INDEX IF NOT EXISTS `idx_storename_reportdate_sync` ON `inventory_summaries` (`storename`, `report_date`, `sync`);

-- Show the updated table structure
DESCRIBE `inventory_summaries`;

-- Show count of unsynced records
SELECT
    storename,
    DATE(report_date) as report_date,
    COUNT(*) as unsynced_count
FROM `inventory_summaries`
WHERE `sync` = 0
GROUP BY storename, DATE(report_date)
ORDER BY report_date DESC, storename;
