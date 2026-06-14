<?php
// GET /api/cursos
require_once __DIR__ . '/../helpers.php';

setCorsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET')
    responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);

$db   = getDB();
$stmt = $db->query(
    "SELECT
        c.id,
        c.nome,
        c.descricao,
        u.nome  AS professor_nome,
        COUNT(a.id) AS total_aulas
     FROM cursos c
     JOIN usuarios u   ON u.id  = c.professor_id
     LEFT JOIN aulas a ON a.curso_id = c.id
     WHERE c.ativo = 1
     GROUP BY c.id, c.nome, c.descricao, u.nome
     ORDER BY c.nome ASC"
);

responder($stmt->fetchAll());
