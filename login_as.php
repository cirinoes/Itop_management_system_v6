<?php
require __DIR__ . '/app/bootstrap.php';
$_SESSION['user'] = [
    'id' => 1,
    'name' => 'Demo ' . ucfirst($_GET['role']),
    'email' => 'demo@example.com',
    'role_slug' => $_GET['role']
];
echo session_id();
