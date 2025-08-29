<?php
$host = 'localhost'; 
$username = 'u818562152_ecpos';
$password = 'Dmn!mrpa_20';
$database = 'u818562152_ecpos';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$today = date('Y-m-d');

$sql = "UPDATE inventory_summaries 
SET throw_away = (
    SELECT SUM(WASTECOUNT) 
    FROM stockcountingtrans 
    WHERE stockcountingtrans.ITEMID = inventory_summaries.itemid
      AND STORENAME = 'COMMUNITY'
      AND CAST(TRANSDATE AS DATE) = '2025-07-16'
)
WHERE CAST(report_date AS DATE) = '2025-07-16'
  AND storename = 'COMMUNITY'
  AND EXISTS (
    SELECT 1 
    FROM stockcountingtrans 
    WHERE stockcountingtrans.itemid = inventory_summaries.itemid
      AND STORENAME = 'COMMUNITY'
      AND CAST(TRANSDATE AS DATE) = '2025-07-16'
);";

if ($conn->query($sql) === TRUE) {
    echo "Inventory summary updated successfully for date: $today\n";
    echo "Affected rows: " . $conn->affected_rows . "\n";
} else {
    echo "Error updating inventory: " . $conn->error . "\n";
}

$conn->close();

$log_message = date('Y-m-d H:i:s') . " - Inventory update executed for date: $today\n";
file_put_contents('/path/to/your/logs/inventory_update.log', $log_message, FILE_APPEND);
?>