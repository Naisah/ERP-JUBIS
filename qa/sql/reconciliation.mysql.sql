-- READ ONLY. Run on a disposable MySQL8 copy after the documented workflows/races.
-- Every anomaly query should return zero rows. Keep the intended opening balance
-- separately: legacy/imported stock cannot be reconciled from movements alone.
START TRANSACTION READ ONLY;
SELECT id, sku, stock_quantity FROM products WHERE deleted_at IS NULL AND stock_quantity < 0;
SELECT quote_id, COUNT(*) AS invoices_per_quote FROM invoices GROUP BY quote_id HAVING COUNT(*) > 1;
SELECT invoice_id, COUNT(*) AS shipments_per_invoice FROM shipments GROUP BY invoice_id HAVING COUNT(*) > 1;
SELECT invoice_id, COUNT(*) AS requests_per_invoice FROM r_m_a_s GROUP BY invoice_id HAVING COUNT(*) > 1;
SELECT id, total_amount, amount_paid, status FROM invoices
 WHERE amount_paid < 0 OR amount_paid > total_amount
 OR (status='paid' AND amount_paid <> total_amount)
 OR (status='partially_paid' AND (amount_paid<=0 OR amount_paid>=total_amount));
SELECT q.id, q.subtotal, COALESCE(SUM(qi.quantity*qi.quoted_price),0) AS expected_subtotal
 FROM quotes q LEFT JOIN quote_items qi ON qi.quote_id=q.id
 GROUP BY q.id, q.subtotal HAVING ABS(q.subtotal-ROUND(expected_subtotal,2))>=0.01;
SELECT id, subtotal, vat_amount, total_amount FROM quotes
 WHERE ABS(vat_amount-ROUND(subtotal*0.12,2))>=0.01 OR ABS(total_amount-(subtotal+vat_amount))>=0.01;
SELECT i.id, i.quote_id, i.subtotal, i.vat_amount, i.total_amount FROM invoices i JOIN quotes q ON q.id=i.quote_id
 WHERE i.subtotal<>q.subtotal OR i.vat_amount<>q.vat_amount OR i.total_amount<>q.total_amount;
SELECT po.id, po.total_cost, COALESCE(SUM(poi.quantity*poi.unit_cost),0) AS expected_cost
 FROM purchase_orders po LEFT JOIN purchase_order_items poi ON poi.purchase_order_id=po.id
 GROUP BY po.id, po.total_cost HAVING ABS(po.total_cost-ROUND(expected_cost,2))>=0.01;
SELECT sm.id, sm.product_id, sm.user_id, sm.reference_id, sm.created_at FROM stock_movements sm
 LEFT JOIN products p ON p.id=sm.product_id LEFT JOIN users u ON u.id=sm.user_id
 WHERE p.id IS NULL OR u.id IS NULL OR sm.created_at IS NULL OR sm.reference_id IS NULL;
SELECT reference_id, product_id, type, COUNT(*) AS repeats FROM stock_movements
 WHERE type='sale' GROUP BY reference_id, product_id, type HAVING COUNT(*)>1;
-- Summary for comparison to reports. Receivables must include partial/on_terms/overdue balances.
SELECT SUM(total_amount-amount_paid) AS open_receivables FROM invoices
 WHERE status IN ('unpaid','partially_paid','on_terms','overdue');
SELECT SUM(total_amount) AS paid_invoice_sales FROM invoices WHERE status='paid';
SELECT SUM(stock_quantity*wholesale_price) AS active_inventory_value FROM products
 WHERE deleted_at IS NULL AND is_active=1 AND stock_quantity>0;
SELECT product_id, SUM(quantity) AS net_recorded_delta FROM stock_movements GROUP BY product_id;
ROLLBACK;
