<?php
// POST /api/auth/login
// Body: { "email": "...", "senha": "..." }
require_once __DIR__ . '/../helpers.php';

setCorsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);

$body  = getBody();
$email = trim($body['email'] ?? '');
$senha = $body['senha']       ?? '';

if (!$email || !$senha)
    responder(['success' => false, 'mensagem' => 'E-mail e senha são obrigatórios.'], 400);

$db   = getDB();
$stmt = $db->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1');
$stmt->execute([$email]);
$u = $stmt->fetch();

if (!$u || !password_verify($senha, $u['senha']))
    responder(['success' => false, 'mensagem' => 'E-mail ou senha incorretos.'], 401);

// Banco v3: tudo em "usuarios", sem tabelas alunos/professores separadas
// Busca matrículas se for aluno
$matriculas = [];
if ($u['tipo'] === 'aluno') {
    $s = $db->prepare(
        'SELECT c.nome AS curso, m.status
         FROM matriculas m
         JOIN cursos c ON c.id = m.curso_id
         WHERE m.aluno_id = ? AND m.status = "ativo"'
    );
    $s->execute([$u['id']]);
    $matriculas = $s->fetchAll() ?: [];
}

$token = gerarToken([
    'id'    => $u['id'],
    'nome'  => $u['nome'],
    'email' => $u['email'],
    'tipo'  => $u['tipo'],
]);

responder([
    'success' => true,
    'token'   => $token,
    'usuario' => [
        'id'         => (int)$u['id'],
        'nome'       => $u['nome'],
        'email'      => $u['email'],
        'tipo'       => $u['tipo'],
        'matriculas' => $matriculas,
    ]
]);
