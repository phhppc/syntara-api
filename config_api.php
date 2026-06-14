<?php
// ============================================================
//  includes/config_api.php  — Syntara v3
//  Coloque este arquivo em: htdocs/includes/config_api.php
//  Usado SOMENTE pela API (sem session_start para não dar 500)
// ============================================================

$host     = 'sql201.infinityfree.com';
$dbname   = 'if0_41933046_syntara';   // ⚠️ confirme o nome exato no painel
$username = 'if0_41933046';
$password = 'tiao340344';             // sua senha do InfinityFree

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'mensagem' => 'Erro de conexão: ' . $e->getMessage()]);
    exit;
}
