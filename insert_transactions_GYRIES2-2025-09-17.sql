-- MySQL INSERT scripts for rbotransactiontables and rbotransactionsalestrans
-- Data from GYRIES2-2025-09-17.txt receipts (SI# 000000765 to 000000769)
-- Note: This appears to be corrected date version of GYRIES transactions

-- Insert into rbotransactiontables
INSERT INTO rbotransactiontables (
    transactionid, type, receiptid, store, staff, cashamount, netamount,
    grossamount, discamount, totaldiscamount, numberofitems, currency,
    createddate, cash
) VALUES
('GYRIES000000765', '0', '000000765', 'GYRIES', 'Jennelyn Tabo', 923.00, 923.00, 1163.00, 240.00, 240.00, 3, 'PHP', '2025-09-17 02:16:12', 923.00),
('GYRIES000000766', '0', '000000766', 'GYRIES', 'Jennelyn Tabo', 888.00, 888.00, 1128.00, 240.00, 240.00, 2, 'PHP', '2025-09-17 02:17:23', 0.00),
('GYRIES000000767', '0', '000000767', 'GYRIES', 'Jennelyn Tabo', 296.00, 296.00, 376.00, 80.00, 80.00, 2, 'PHP', '2025-09-17 03:26:22', 0.00),
('GYRIES000000768', '0', '000000768', 'GYRIES', 'Jennelyn Tabo', 158.00, 158.00, 198.00, 40.00, 40.00, 3, 'PHP', '2025-09-17 03:26:36', 158.00),
('GYRIES000000769', '0', '000000769', 'GYRIES', 'Jennelyn Tabo', 395.00, 395.00, 495.00, 100.00, 100.00, 2, 'PHP', '2025-09-17 04:02:05', 395.00);

-- Insert into rbotransactionsalestrans
INSERT INTO rbotransactionsalestrans (
    transactionid, linenum, receiptid, itemname, price, netprice, qty,
    discamount, netamount, grossamount, store, staff, createddate
) VALUES
-- Transaction 000000765 items
('GYRIES000000765', 1, '000000765', 'GYRIES SHAWARMA', 89.00, 69.00, 12, 240.00, 828.00, 1068.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 02:16:12'),
('GYRIES000000765', 2, '000000765', 'GYRIES CHEESE', 10.00, 10.00, 8, 0.00, 80.00, 80.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 02:16:12'),
('GYRIES000000765', 3, '000000765', 'GYRIES MEAT', 15.00, 15.00, 1, 0.00, 15.00, 15.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 02:16:12'),

-- Transaction 000000766 items
('GYRIES000000766', 1, '000000766', 'GYRIES SHAWARMA', 89.00, 69.00, 12, 240.00, 828.00, 1068.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 02:17:23'),
('GYRIES000000766', 2, '000000766', 'GYRIES CHEESE', 10.00, 10.00, 6, 0.00, 60.00, 60.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 02:17:23'),

-- Transaction 000000767 items
('GYRIES000000767', 1, '000000767', 'GYRIES SHAWARMA', 89.00, 69.00, 4, 80.00, 276.00, 356.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 03:26:22'),
('GYRIES000000767', 2, '000000767', 'GYRIES CHEESE', 10.00, 10.00, 2, 0.00, 20.00, 20.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 03:26:22'),

-- Transaction 000000768 items
('GYRIES000000768', 1, '000000768', 'GYRIES SHAWARMA', 89.00, 69.00, 1, 20.00, 69.00, 89.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 03:26:36'),
('GYRIES000000768', 2, '000000768', 'GYRIES SHAWARMA', 89.00, 69.00, 1, 20.00, 69.00, 89.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 03:26:36'),
('GYRIES000000768', 3, '000000768', 'GYRIES CHEESE', 10.00, 10.00, 2, 0.00, 20.00, 20.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 03:26:36'),

-- Transaction 000000769 items
('GYRIES000000769', 1, '000000769', 'GYRIES SHAWARMA', 89.00, 69.00, 5, 100.00, 345.00, 445.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 04:02:05'),
('GYRIES000000769', 2, '000000769', 'GYRIES CHEESE', 10.00, 10.00, 5, 0.00, 50.00, 50.00, 'GYRIES', 'Jennelyn Tabo', '2025-09-17 04:02:05');