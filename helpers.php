<?php
// ============================================================
//  api/helpers.php — Syntara v3
//  ATUALIZADO: conexão com Aiven MySQL (banco compartilhado)
// ============================================================

$host    = 'syntara-mysql-phhpxbox-7cf9.a.aivencloud.com';
$port    = '20075';
$dbname  = 'syntara_db';
$user    = 'avnadmin';
$pass    = 'AVNS_G3ZS_OTozA_Vh-yWHAG';  // <-- troque pela senha do Aiven
$ca      = __DIR__ . '/ca.pem';       // ca.pem na mesma pasta que helpers.php

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user, $pass,
        [
            PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES         => false,
            PDO::MYSQL_ATTR_SSL_CA             => $ca,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'mensagem' => 'Erro de conexão: ' . $e->getMessage()]);
    exit;
}

define('JWT_SECRET', 'syntara_chave_secreta_tcc_2025_troque_isso');
define('JWT_EXPIRY',  7 * 24 * 3600);

function setCorsHeaders(): void {
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
}

function responder(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getDB(): PDO { global $pdo; return $pdo; }

function getBody(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

function gerarToken(array $payload): string {
    $h = b64u(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['exp'] = time() + JWT_EXPIRY;
    $p = b64u(json_encode($payload));
    return "$h.$p." . b64u(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));
}

function validarToken(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$h, $p, $sig] = $parts;
    if (!hash_equals(b64u(hash_hmac('sha256', "$h.$p", JWT_SECRET, true)), $sig)) return null;
    $d = json_decode(b64d($p), true);
    if (!$d || $d['exp'] < time()) return null;
    return $d;
}

function autenticar(): array {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!str_starts_with($auth, 'Bearer '))
        responder(['success' => false, 'mensagem' => 'Token não fornecido.'], 401);
    $d = validarToken(substr($auth, 7));
    if (!$d)
        responder(['success' => false, 'mensagem' => 'Token inválido ou expirado.'], 401);
    return $d;
}

function exigirAdmin(): array {
    $u = autenticar();
    if ($u['tipo'] !== 'admin')
        responder(['success' => false, 'mensagem' => 'Acesso negado.'], 403);
    return $u;
}

function b64u(string $d): string { return rtrim(strtr(base64_encode($d), '+/', '-_'), '='); }
function b64d(string $d): string { return base64_decode(strtr($d, '-_', '+/')); }
