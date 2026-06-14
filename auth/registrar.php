<?php
// POST /api/auth/registrar
// Body: { nome, email, senha, tipo }
require_once __DIR__ . '/../helpers.php';

setCorsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);

$body  = getBody();
$nome  = trim($body['nome']  ?? '');
$email = trim($body['email'] ?? '');
$senha = $body['senha']       ?? '';
$tipo  = $body['tipo']        ?? 'aluno';

if (!$nome || !$email || !$senha)
    responder(['success' => false, 'mensagem' => 'Nome, e-mail e senha são obrigatórios.'], 400);

if (!in_array($tipo, ['aluno', 'professor', 'admin']))
    responder(['success' => false, 'mensagem' => 'Tipo inválido.'], 400);

$db = getDB();

$s = $db->prepare('SELECT id FROM usuarios WHERE email = ?');
$s->execute([$email]);
if ($s->fetch())
    responder(['success' => false, 'mensagem' => 'E-mail já cadastrado.'], 409);

$db->prepare('INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)')
   ->execute([$nome, $email, password_hash($senha, PASSWORD_BCRYPT), $tipo]);

responder(['success' => true, 'mensagem' => 'Usuário cadastrado!', 'id' => (int)$db->lastInsertId()], 201);
