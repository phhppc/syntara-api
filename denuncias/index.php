<?php
// POST /api/denuncias
// Body: { "tipo": "...", "descricao": "..." }
// 100% anônimo — sem aluno_id
require_once __DIR__ . '/../helpers.php';

setCorsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);

$body      = getBody();
$tipo      = trim($body['tipo']      ?? 'geral');
$descricao = trim($body['descricao'] ?? '');

if (strlen($descricao) < 5)
    responder(['success' => false, 'mensagem' => 'Escreva a denúncia.'], 400);

// Código único para acompanhamento (ex: "A1B2C3D4E5F6")
$codigo = strtoupper(bin2hex(random_bytes(6)));

getDB()->prepare(
    'INSERT INTO denuncias (codigo, tipo, descricao) VALUES (?, ?, ?)'
)->execute([$codigo, $tipo, $descricao]);

responder([
    'success'  => true,
    'mensagem' => 'Denúncia registrada com sucesso!',
    'codigo'   => $codigo,
]);
