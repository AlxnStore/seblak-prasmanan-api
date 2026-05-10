<?php
require_once 'config.php';
require_once 'cors.php';
setCorsHeaders();

$action = $_GET['action'] ?? 'get';
$db     = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['user_id'] ?? '';
    if ($userId) {
        $stmt = $db->prepare('SELECT id, user_id, user_name, total_price, status, created_at, notes, table_number, order_type, payment_method FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->query('SELECT id, user_id, user_name, total_price, status, created_at, notes, table_number, order_type, payment_method FROM orders ORDER BY created_at DESC');
    }

    $orders = [];
    while ($order = $stmt->fetch()) {
        $order['total_price'] = (float) $order['total_price'];
        $itemStmt = $db->prepare('SELECT menu_item_id, menu_item_name, menu_item_price, menu_item_emoji, quantity FROM order_items WHERE order_id = ?');
        $itemStmt->execute([$order['id']]);
        $items = [];
        while ($item = $itemStmt->fetch()) {
            $item['menu_item_price'] = (float) $item['menu_item_price'];
            $item['quantity']        = (int)   $item['quantity'];
            $items[] = $item;
        }
        $order['items'] = $items;
        $orders[] = $order;
    }
    jsonOk(['data' => $orders]);
}

$body = getBody();

switch ($action) {
    case 'place':
        $userId        = $body['userId'] ?? '';
        $userName      = $body['userName'] ?? '';
        $totalPrice    = (float) ($body['totalPrice'] ?? 0);
        $notes         = $body['notes'] ?? null;
        $tableNumber   = $body['tableNumber'] ?? '1';
        $orderType     = $body['orderType'] ?? 'dine_in';
        $paymentMethod = $body['paymentMethod'] ?? 'qris';
        $items         = $body['items'] ?? [];

        if (!$userId || !$userName || empty($items)) jsonError('Data pesanan tidak lengkap');

        $orderId = uniqid('ord_', true);
        $stmt = $db->prepare('INSERT INTO orders (id, user_id, user_name, total_price, status, created_at, notes, table_number, order_type, payment_method) VALUES (?, ?, ?, ?, "pending", NOW(), ?, ?, ?, ?)');
        $stmt->execute([$orderId, $userId, $userName, $totalPrice, $notes, $tableNumber, $orderType, $paymentMethod]);

        $iStmt = $db->prepare('INSERT INTO order_items (order_id, menu_item_id, menu_item_name, menu_item_price, menu_item_emoji, quantity) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($items as $item) {
            $iStmt->execute([$orderId, $item['menuItemId'] ?? '', $item['menuItemName'] ?? '', (float)($item['menuItemPrice'] ?? 0), $item['menuItemEmoji'] ?? '', (int)($item['quantity'] ?? 1)]);
        }
        jsonOk(['orderId' => $orderId], 'Pesanan berhasil dibuat');
        break;

    case 'update_status':
        $orderId   = $body['orderId'] ?? '';
        $newStatus = $body['status'] ?? '';
        if (!$orderId || !in_array($newStatus, ['pending','preparing','ready','completed','cancelled'])) jsonError('Data tidak valid');

        $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $orderId]);
        jsonOk([], 'Status diperbarui');
        break;

    case 'cancel':
        $orderId = $body['orderId'] ?? '';
        if (!$orderId) jsonError('Order ID wajib diisi');

        $check = $db->prepare('SELECT status FROM orders WHERE id = ? LIMIT 1');
        $check->execute([$orderId]);
        $row = $check->fetch();
        if (!$row) jsonError('Pesanan tidak ditemukan', 404);
        if ($row['status'] !== 'pending') jsonError('Hanya pesanan pending yang bisa dibatalkan');

        $stmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$orderId]);
        jsonOk([], 'Pesanan dibatalkan');
        break;

    default:
        jsonError('Action tidak dikenal', 404);
}
