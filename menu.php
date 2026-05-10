<?php
// ============================================================
//  menu.php — CRUD Menu Items
// ============================================================

require_once 'config.php';
require_once 'cors.php';

setCorsHeaders();

$action = $_GET['action'] ?? 'get';
$db     = getDB();

// GET ALL MENU
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $db->query(
        'SELECT id, name, description, price, category, emoji,
                is_available, stock, image_url
         FROM menu_items
         ORDER BY category, name'
    );

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['price']        = (float) $row['price'];
        $row['stock']        = (int)   $row['stock'];
        $row['is_available'] = (bool)  $row['is_available'];
        $items[] = $row;
    }

    jsonOk(['data' => $items]);
}

$body = getBody();

switch ($action) {

    case 'add':
        $id          = $body['id']          ?? uniqid('menu_', true);
        $name        = trim($body['name']   ?? '');
        $description = trim($body['description'] ?? '');
        $price       = (float) ($body['price'] ?? 0);
        $category    = trim($body['category'] ?? '');
        $emoji       = trim($body['emoji']    ?? '🍲');
        $isAvailable = isset($body['isAvailable']) ? (int)(bool)$body['isAvailable'] : 1;
        $stock       = (int) ($body['stock'] ?? 100);
        $imageUrl    = $body['imageUrl'] ?? null;

        if (!$name || !$category || $price <= 0) {
            jsonError('Nama, kategori, dan harga wajib diisi');
        }

        $stmt = $db->prepare(
            'INSERT INTO menu_items (id, name, description, price, category, emoji, is_available, stock, image_url)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssdssiis',
            $id, $name, $description, $price, $category, $emoji, $isAvailable, $stock, $imageUrl
        );

        if (!$stmt->execute()) {
            jsonError('Gagal menambah menu: ' . $db->error, 500);
        }
        jsonOk([], 'Menu berhasil ditambahkan');
        break;

    case 'update':
        $id          = $body['id'] ?? '';
        $name        = trim($body['name']        ?? '');
        $description = trim($body['description'] ?? '');
        $price       = (float) ($body['price']   ?? 0);
        $category    = trim($body['category']    ?? '');
        $emoji       = trim($body['emoji']       ?? '🍲');
        $isAvailable = isset($body['isAvailable']) ? (int)(bool)$body['isAvailable'] : 1;
        $stock       = (int) ($body['stock']     ?? 100);
        $imageUrl    = $body['imageUrl'] ?? null;

        if (!$id) {
            jsonError('ID menu wajib diisi');
        }

        $stmt = $db->prepare(
            'UPDATE menu_items
             SET name=?, description=?, price=?, category=?, emoji=?,
                 is_available=?, stock=?, image_url=?
             WHERE id=?'
        );
        $stmt->bind_param('ssdssiiis',
            $name, $description, $price, $category, $emoji, $isAvailable, $stock, $imageUrl, $id
        );

        if (!$stmt->execute()) {
            jsonError('Gagal update menu: ' . $db->error, 500);
        }
        jsonOk([], 'Menu berhasil diperbarui');
        break;

    case 'delete':
        $id = $body['id'] ?? '';
        if (!$id) {
            jsonError('ID menu wajib diisi');
        }

        $stmt = $db->prepare('DELETE FROM menu_items WHERE id = ?');
        $stmt->bind_param('s', $id);

        if (!$stmt->execute()) {
            jsonError('Gagal menghapus menu: ' . $db->error, 500);
        }
        jsonOk([], 'Menu berhasil dihapus');
        break;

    default:
        jsonError('Action tidak dikenal', 404);
}

$db->close();
