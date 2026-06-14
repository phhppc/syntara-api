<?php
// POST /api/avaliacoes
// GET  /api/avaliacoes/minhas
require_once __DIR__ . '/../helpers.php';

setCorsHeaders();
$db  = getDB();
$m   = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// ── GET /api/avaliacoes/minhas ─────────────────────────────────
if ($m === 'GET' && str_contains($uri, 'minhas')) {
    $u    = autenticar();
    $stmt = $db->prepare(
        "SELECT
            av.id,
            av.nota,
            av.comentario,
            DATE_FORMAT(av.criado_em, '%d/%m/%Y') AS data_avaliacao,
            p.nome  AS professor_nome,
            c.nome  AS curso_nome
         FROM avaliacoes av
         JOIN usuarios p ON p.id  = av.professor_id
         JOIN cursos   c ON c.id  = av.curso_id
         WHERE av.aluno_id = ?
         ORDER BY av.criado_em DESC"
    );
    $stmt->execute([$u['id']]);
    responder($stmt->fetchAll());
}

// ── POST /api/avaliacoes ───────────────────────────────────────
if ($m === 'POST') {
    $u    = autenticar();
    $body = getBody();

    $professor_id = (int)($body['professor_id'] ?? 0);
    $nota         = (float)($body['nota']        ?? 0);
    $comentario   = trim($body['comentario']      ?? '');

    if (!$professor_id)
        responder(['success' => false, 'mensagem' => 'professor_id é obrigatório.'], 400);
    if ($nota < 1 || $nota > 5)
        responder(['success' => false, 'mensagem' => 'Nota deve ser entre 1 e 5.'], 400);

    // Busca curso do professor em que o aluno está matriculado
    $s = $db->prepare(
        "SELECT c.id FROM cursos c
         JOIN matriculas m ON m.curso_id = c.id
         WHERE c.professor_id = ? AND m.aluno_id = ? AND m.status = 'ativo'
         LIMIT 1"
    );
    $s->execute([$professor_id, $u['id']]);
    $row = $s->fetch();

    // Se não tiver matrícula formal, pega o primeiro curso do professor
    if (!$row) {
        $s2 = $db->prepare('SELECT id FROM cursos WHERE professor_id = ? AND ativo = 1 LIMIT 1');
        $s2->execute([$professor_id]);
        $row = $s2->fetch();
    }

    if (!$row)
        responder(['success' => false, 'mensagem' => 'Nenhum curso encontrado para este professor.'], 404);

    $curso_id = (int)$row['id'];

    // Upsert: atualiza se já existir
    $s = $db->prepare(
        'SELECT id FROM avaliacoes WHERE aluno_id = ? AND professor_id = ? AND curso_id = ?'
    );
    $s->execute([$u['id'], $professor_id, $curso_id]);

    if ($s->fetch()) {
        $db->prepare(
            'UPDATE avaliacoes SET nota = ?, comentario = ?, atualizado_em = NOW()
             WHERE aluno_id = ? AND professor_id = ? AND curso_id = ?'
        )->execute([$nota, $comentario, $u['id'], $professor_id, $curso_id]);
        responder(['success' => true, 'mensagem' => 'Avaliação atualizada!']);
    }

    $db->prepare(
        'INSERT INTO avaliacoes (aluno_id, professor_id, curso_id, nota, comentario)
         VALUES (?, ?, ?, ?, ?)'
    )->execute([$u['id'], $professor_id, $curso_id, $nota, $comentario]);

    responder(['success' => true, 'mensagem' => 'Avaliação enviada com sucesso!'], 201);
}

responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);
