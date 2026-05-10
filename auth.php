<?php
// ============================================================
//  auth.php — Login & Register
// ============================================================

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

        if (!$email || !$password) {
            jsonError('Email dan password wajib diisi');
        }

        $stmt = $db->prepare(
            'SELECT id, name, email, password, role, phone FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        if (!$user) {
            jsonError('Email tidak terdaftar');
        }

        $passwordMatch = password_verify($password, $user['password'])
                      || $password === $user['password'];

        if (!$passwordMatch) {
            jsonError('Password salah');
        }

        unset($user['password']);
        jsonOk(['user' => $user], 'Login berhasil');
        break;

    case 'register':
        $name     = trim($body['name'] ?? '');
        $email    = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';
        $phone    = trim($body['phone'] ?? '');

        if (!$name || !$email || !$password) {
            jsonError('Nama, email, dan password wajib diisi');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonError('Format email tidak valid');
        }

        if (strlen($password) < 6) {
            jsonError('Password minimal 6 karakter');
        }

        $check = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            jsonError('Email sudah terdaftar');
        }

        $id         = uniqid('usr_', true);
        $hashedPass = password_hash($password, PASSWORD_BCRYPT);
        $role       = 'customer';

        $ins = $db->prepare(
            'INSERT INTO users (id, name, email, password, role, phone) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $ins->bind_param('ssssss', $id, $name, $email, $hashedPass, $role, $phone);

        if (!$ins->execute()) {
            jsonError('Gagal mendaftar: ' . $db->error, 500);
        }

        $newUser = [
            'id'    => $id,
            'name'  => $name,
            'email' => $email,
            'role'  => $role,
            'phone' => $phone,
        ];
        jsonOk(['user' => $newUser], 'Registrasi berhasil');
        break;

    default:
        jsonError('Action tidak dikenal', 404);
}

$db->close();
