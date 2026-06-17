<?php
// PUT /api/usuarios/senha
require_once __DIR__ . '/../helpers.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

if ($_SERVER['REQUEST_METHOD'] !== 'PUT')
    responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);

$usuario = autenticar();
$body    = getBody();

$senhaAtual = $body['senha_atual'] ?? '';
$novaSenha  = $body['nova_senha']  ?? '';

if (!$senhaAtual || !$novaSenha)
    responder(['success' => false, 'mensagem' => 'Preencha todos os campos.'], 400);

if (strlen($novaSenha) < 6)
    responder(['success' => false, 'mensagem' => 'A nova senha deve ter ao menos 6 caracteres.'], 400);

$db   = getDB();
$stmt = $db->prepare('SELECT senha FROM usuarios WHERE id = ? AND ativo = 1');
$stmt->execute([$usuario['id']]);
$u    = $stmt->fetch();

if (!$u || !password_verify($senhaAtual, $u['senha']))
    responder(['success' => false, 'mensagem' => 'Senha atual incorreta.'], 401);

$hash = password_hash($novaSenha, PASSWORD_DEFAULT);
$db->prepare('UPDATE usuarios SET senha = ? WHERE id = ?')
   ->execute([$hash, $usuario['id']]);

responder(['success' => true, 'mensagem' => 'Senha alterada com sucesso!']);
