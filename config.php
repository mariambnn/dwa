<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hkyfpmwmes_dwa');
define('DB_USER', 'hkyfpmwmes');
define('DB_PASS', 'GzfPNm3kWAX65FCyn7YjreKv');
define('TVA', 1.21);

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}