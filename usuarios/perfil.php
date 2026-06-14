<?php
// GET /api/usuarios/perfil
// PUT /api/usuarios/perfil  Body: { nome }
require_once __DIR__ . '/../helpers.php';

setCorsHeaders();
$u  = autenticar();
$db = getDB();
$m  = $_SERVER['REQUEST_METHOD'];

if ($m === 'GET') {
    $stmt = $db->prepare(
        'SELECT id, nome, email, tipo, criado_em FROM usuarios WHERE id = ?'
    );
    $stmt->execute([$u['id']]);
    $usuario = $stmt->fetch();
    if (!$usuario)
        responder(['success' => false, 'mensagem' => 'Usuário não encontrado.'], 404);

    // Se aluno: busca cursos matriculados
    $cursos = [];
    if ($usuario['tipo'] === 'aluno') {
        $s = $db->prepare(
            'SELECT c.id, c.nome, m.status
             FROM matriculas m
             JOIN cursos c ON c.id = m.curso_id
             WHERE m.aluno_id = ? AND m.status = "ativo"'
        );
        $s->execute([$u['id']]);
        $cursos = $s->fetchAll() ?: [];
    }

    // Se professor: busca seus cursos
    if ($usuario['tipo'] === 'professor') {
        $s = $db->prepare(
            'SELECT id, nome FROM cursos WHERE professor_id = ? AND ativo = 1'
        );
        $s->execute([$u['id']]);
        $cursos = $s->fetchAll() ?: [];
    }

    responder([
        'success' => true,
        'usuario' => array_merge($usuario, ['cursos' => $cursos])
    ]);
}

if ($m === 'PUT') {
    $body = getBody();
    $nome = trim($body['nome'] ?? '');
    if (!$nome) responder(['success' => false, 'mensagem' => 'Nome é obrigatório.'], 400);
    $db->prepare('UPDATE usuarios SET nome = ? WHERE id = ?')->execute([$nome, $u['id']]);
    responder(['success' => true, 'mensagem' => 'Perfil atualizado!']);
}

responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);
