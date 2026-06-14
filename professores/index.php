<?php
// GET /api/professores
// Retorna usuarios com tipo='professor' + média de avaliação
require_once __DIR__ . '/../helpers.php';

setCorsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET')
    responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);

$db   = getDB();
$stmt = $db->query(
    "SELECT
        u.id,
        u.nome,
        u.email,
        ROUND(COALESCE(AVG(a.nota), 0), 1) AS media_avaliacao,
        COUNT(a.id)                          AS total_avaliacoes
     FROM usuarios u
     LEFT JOIN avaliacoes a ON a.professor_id = u.id
     WHERE u.tipo = 'professor' AND u.ativo = 1
     GROUP BY u.id, u.nome, u.email
     ORDER BY u.nome ASC"
);

responder($stmt->fetchAll());
