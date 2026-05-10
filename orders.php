<?php
// ============================================================
//  orders.php — Kelola Pesanan
// ============================================================

require_once 'config.php';
require_once 'cors.php';

setCorsHeaders();

$action = $_GET['action'] ?? 'get';
$db     = getDB();

// GET ORDERS
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['user_id'] ?? '';

    if ($userId) {
        $stmt = $db->prepare(
            'SELECT o.id, o.user_id, o.user_name, o.total_price, o.status,
                    o.created_at, o.notes, o.table_number, o.order_type, o.payment_method
             FROM orders o
             WHERE o.user_id = ?
             ORDER BY o.created_at DESC'
        );
        $stmt->bind_param('s', $userId);
    } else {
        $stmt = $db->prepare(
            'SELECT o.id, o.user_id, o.user_name, o.total_price, o.status,
                    o.created_at, o.notes, o.table_number, o.order_type, o.payment_method
             FROM orders o
             ORDER BY o.created_at DESC'
        );
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];

    while ($order = $result->fetch_assoc()) {
        $order['total_price'] = (float) $order['total_price'];

        $itemStmt = $db->prepare(
            'SELECT menu_item_id, menu_item_name, menu_item_price, menu_item_emoji, quantity
             FROM order_items
             WHERE order_id = ?'
        );
        $itemStmt->bind_param('s', $order['id']);
        $itemStmt->execute();
        $itemResult = $itemStmt->get_result();

        $items = [];
        while ($item = $itemResult->fetch_assoc()) {
            $item['menu_item_price'] = (float) $item['menu_item_price'];
            $item['quantity']        = (int)   $item['quantity'];
            $items[] = $item;
        }

        $order['items'] = $items;
        $orders[]       = $order;
    }

    jsonOk(['data' => $orders]);
}

$body = getBody();

switch ($action) {

    case 'place':
        $userId        = $body['userId']        ?? '';
        $userName      = $body['userName']      ?? '';
        $totalPrice    = (float) ($body['totalPrice'] ?? 0);
        $notes         = $body['notes']         ?? null;
        $tableNumber   = $body['tableNumber']   ?? '1';
        $orderType     = $body['orderType']     ?? 'dine_in';
        $paymentMethod = $body['paymentMethod'] ?? 'qris';
        $items         = $body['items']         ?? [];

        if (!$userId || !$userName || empty($items)) {
            jsonError('Data pesanan tidak lengkap');
        }

        $orderId = uniqid('ord_', true);

        $stmt = $db->prepare(
            'INSERT INTO orders
             (id, user_id, user_name, total_price, status, created_at, notes, table_number, order_type, payment_method)
             VALUES (?, ?, ?, ?, "pending", NOW(), ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssdssss',
            $orderId, $userId, $userName, $totalPrice,
            $notes, $tableNumber, $orderType, $paymentMethod
        );

        if (!$stmt->execute()) {
            jsonError('Gagal menyimpan pesanan: ' . $db->error, 500);
        }

        foreach ($items as $item) {
            $menuItemId    = $item['menuItemId']    ?? '';
            $menuItemName  = $item['menuItemName']  ?? '';
            $menuItemPrice = (float) ($item['menuItemPrice'] ?? 0);
            $menuItemEmoji = $item['menuItemEmoji'] ?? '';
            $quantity      = (int) ($item['quantity'] ?? 1);
            $subtotal      = $menuItemPrice * $quantity;

            $iStmt = $db->prepare(
                'INSERT INTO order_items
                 (order_id, menu_item_id, menu_item_name, menu_item_price, menu_item_emoji, quantity)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $iStmt->bind_param('sssdsi',
                $orderId, $menuItemId, $menuItemName,
                $menuItemPrice, $menuItemEmoji, $quantity
            );
            $iStmt->execute();
        }

        jsonOk(['orderId' => $orderId], 'Pesanan berhasil dibuat');
        break;

    case 'update_status':
        $orderId   = $body['orderId'] ?? '';
        $newStatus = $body['status']  ?? '';

        $allowed = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
        if (!$orderId || !in_array($newStatus, $allowed)) {
            jsonError('Order ID atau status tidak valid');
        }

        $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->bind_param('ss', $newStatus, $orderId);

        if (!$stmt->execute()) {
            jsonError('Gagal update status: ' . $db->error, 500);
        }
        jsonOk([], 'Status pesanan diperbarui');
        break;

    case 'cancel':
        $orderId = $body['orderId'] ?? '';
        if (!$orderId) {
            jsonError('Order ID wajib diisi');
        }

        $check = $db->prepare('SELECT status FROM orders WHERE id = ? LIMIT 1');
        $check->bind_param('s', $orderId);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();

        if (!$row) {
            jsonError('Pesanan tidak ditemukan', 404);
        }
        if ($row['status'] !== 'pending') {
            jsonError('Hanya pesanan dengan status "pending" yang bisa dibatalkan');
        }

        $stmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param('s', $orderId);

        if (!$stmt->execute()) {
            jsonError('Gagal membatalkan pesanan: ' . $db->error, 500);
        }
        jsonOk([], 'Pesanan dibatalkan');
        break;

    default:
        jsonError('Action tidak dikenal', 404);
}

$db->close();
