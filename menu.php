<?php
require_once 'config.php';
require_once 'cors.php';
setCorsHeaders();

$action = $_GET['action'] ?? 'get';
$db     = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->query('SELECT id, name, description, price, category, emoji, is_available, stock, image_url FROM menu_items ORDER BY category, name');
    $items = [];
    while ($row = $stmt->fetch()) {
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
        $id          = $body['id'] ?? uniqid('menu_', true);
        $name        = trim($body['name'] ?? '');
        $description = trim($body['description'] ?? '');
        $price       = (float) ($body['price'] ?? 0);
        $category    = trim($body['category'] ?? '');
        $emoji       = trim($body['emoji'] ?? '🍲');
        $isAvailable = isset($body['isAvailable']) ? (int)(bool)$body['isAvailable'] : 1;
        $stock       = (int) ($body['stock'] ?? 100);
        $imageUrl    = $body['imageUrl'] ?? null;

        if (!$name || !$category || $price <= 0) jsonError('Nama, kategori, dan harga wajib diisi');

        $stmt = $db->prepare('INSERT INTO menu_items (id, name, description, price, category, emoji, is_available, stock, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$id, $name, $description, $price, $category, $emoji, $isAvailable, $stock, $imageUrl]);
        jsonOk([], 'Menu berhasil ditambahkan');
        break;

    case 'update':
        $id          = $body['id'] ?? '';
        $name        = trim($body['name'] ?? '');
        $description = trim($body['description'] ?? '');
        $price       = (float) ($body['price'] ?? 0);
        $category    = trim($body['category'] ?? '');
        $emoji       = trim($body['emoji'] ?? '🍲');
        $isAvailable = isset($body['isAvailable']) ? (int)(bool)$body['isAvailable'] : 1;
        $stock       = (int) ($body['stock'] ?? 100);
        $imageUrl    = $body['imageUrl'] ?? null;

        if (!$id) jsonError('ID menu wajib diisi');

        $stmt = $db->prepare('UPDATE menu_items SET name=?, description=?, price=?, category=?, emoji=?, is_available=?, stock=?, image_url=? WHERE id=?');
        $stmt->execute([$name, $description, $price, $category, $emoji, $isAvailable, $stock, $imageUrl, $id]);
        jsonOk([], 'Menu berhasil diperbarui');
        break;

    case 'delete':
        $id = $body['id'] ?? '';
        if (!$id) jsonError('ID menu wajib diisi');

        $stmt = $db->prepare('DELETE FROM menu_items WHERE id = ?');
        $stmt->execute([$id]);
        jsonOk([], 'Menu berhasil dihapus');
        break;

    default:
        jsonError('Action tidak dikenal', 404);
}
