-- MySQL INSERT scripts for rbotransactiontables and rbotransactionsalestrans
-- Data from GYRIES2-2025-09-18.txt receipts (SI# 000000770 to 000000775)
-- Note: This appears to be corrected date version of GYRIES transactions

-- Insert into rbotransactiontables
INSERT INTO rbotransactiontables (
    transactionid, type, receiptid, store, staff, cashamount, netamount,
    grossamount, discamount, totaldiscamount, numberofitems, currency,
    createddate, cash
) VALUES
('GYRIES000000770', '0', '000000770', 'GYRIES', 'Jennelyn Tabo', 1431.00, 1431.00, 1811.00, 380.00, 380.00, 2, 'PHP', '2025-09-18 01:35:22', 1431.00),
('GYRIES000000771', '0', '000000771', 'GYRIES', 'Jennelyn Tabo', 1697.00, 1697.00, 2157.00, 460.00, 460.00, 2, 'PHP', '2025-09-18 01:36:14', 0.00),
('GYRIES000000772', '0', '000000772', 'GYRIES', 'Jennelyn Tabo', 158.00, 158.00, 198.00, 40.00, 40.00, 3, 'PHP', '2025-09-18 01:50:28', 158.00),
('GYRIES000000773', '0', '000000773', 'GYRIES', 'Jennelyn Tabo', 286.00, 286.00, 366.00, 80.00, 80.00, 2, 'PHP', '2025-09-18 02:09:50', 286.00),
('GYRIES000000774', '0', '000000774', 'GYRIES', 'Jennelyn Tabo', 228.00, 228.00, 268.00, 40.00, 40.00, 2, 'PHP', '2025-09-18 03:41:30', 228.00),
('GYRIES000000775', '0', '000000775', 'GYRIES', 'Jennelyn Tabo', 513.00, 513.00, 653.00, 140.00, 140.00, 2, 'PHP', '2025-09-18 03:42:55', 0.00);

-- Insert into rbotransactionsalestrans
INSERT INTO rbotransactionsalestrans (
    transactionid, linenum, receiptid, itemname, price, netprice, qty,
    discamount, netamount, grossamount, store, staff, createddate
) VALUES
-- Transaction 000000770 items
('GYRIES000000770', 1, '000000770', 'GYRIES SHAWARMA', 89.00, 69.00, 19, 380.00, 1311.00, 1691.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 01:35:22'),
('GYRIES000000770', 2, '000000770', 'GYRIES CHEESE', 10.00, 10.00, 12, 0.00, 120.00, 120.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 01:35:22'),

-- Transaction 000000771 items
('GYRIES000000771', 1, '000000771', 'GYRIES SHAWARMA', 89.00, 69.00, 23, 460.00, 1587.00, 2047.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 01:36:14'),
('GYRIES000000771', 2, '000000771', 'GYRIES CHEESE', 10.00, 10.00, 11, 0.00, 110.00, 110.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 01:36:14'),

-- Transaction 000000772 items
('GYRIES000000772', 1, '000000772', 'GYRIES SHAWARMA', 89.00, 69.00, 1, 20.00, 69.00, 89.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 01:50:28'),
('GYRIES000000772', 2, '000000772', 'GYRIES SHAWARMA', 89.00, 69.00, 1, 20.00, 69.00, 89.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 01:50:28'),
('GYRIES000000772', 3, '000000772', 'GYRIES CHEESE', 10.00, 10.00, 2, 0.00, 20.00, 20.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 01:50:28'),

-- Transaction 000000773 items
('GYRIES000000773', 1, '000000773', 'GYRIES SHAWARMA', 89.00, 69.00, 4, 80.00, 276.00, 356.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 02:09:50'),
('GYRIES000000773', 2, '000000773', 'GYRIES CHEESE', 10.00, 10.00, 1, 0.00, 10.00, 10.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 02:09:50'),

-- Transaction 000000774 items
('GYRIES000000774', 1, '000000774', 'GYRIES SHAWARMA', 89.00, 69.00, 2, 40.00, 138.00, 178.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 03:41:30'),
('GYRIES000000774', 2, '000000774', 'GYRIES MEAT', 15.00, 15.00, 6, 0.00, 90.00, 90.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 03:41:30'),

-- Transaction 000000775 items
('GYRIES000000775', 1, '000000775', 'GYRIES SHAWARMA', 89.00, 69.00, 7, 140.00, 483.00, 623.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 03:42:55'),
('GYRIES000000775', 2, '000000775', 'GYRIES CHEESE', 10.00, 10.00, 3, 0.00, 30.00, 30.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-18 03:42:55');