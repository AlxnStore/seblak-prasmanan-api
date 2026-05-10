<?php
require_once 'config.php';
require_once 'cors.php';
setCorsHeaders();

$action = $_GET['action'] ?? '';
$body   = getBody();
$db     = getDB();

switch ($action) {
    case 'login':
        $email    = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';
        if (!$email || !$password) jsonError('Email dan password wajib diisi');

        $stmt = $db->prepare('SELECT id, name, email, password, role, phone FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) jsonError('Email tidak terdaftar');
        if (!password_verify($password, $user['password']) && $password !== $user['password']) jsonError('Password salah');

        unset($user['password']);
        jsonOk(['user' => $user], 'Login berhasil');
        break;

    case 'register':
        $name     = trim($body['name'] ?? '');
        $email    = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';
        $phone    = trim($body['phone'] ?? '');

        if (!$name || !$email || !$password) jsonError('Nama, email, dan password wajib diisi');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonError('Format email tidak valid');
        if (strlen($password) < 6) jsonError('Password minimal 6 karakter');

        $check = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) jsonError('Email sudah terdaftar');

        $id = uniqid('usr_', true);
        $ins = $db->prepare('INSERT INTO users (id, name, email, password, role, phone) VALUES (?, ?, ?, ?, ?, ?)');
        $ins->execute([$id, $name, $email, password_hash($password, PASSWORD_BCRYPT), 'customer', $phone]);

        jsonOk(['user' => ['id' => $id, 'name' => $name, 'email' => $email, 'role' => 'customer', 'phone' => $phone]], 'Registrasi berhasil');
        break;

    default:
        jsonError('Action tidak dikenal', 404);
}
