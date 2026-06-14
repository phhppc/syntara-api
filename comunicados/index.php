<?php
// GET /api/comunicados
// GET /api/comunicados?mes=X&ano=Y
// GET /api/comunicados?curso_id=X
require_once __DIR__ . '/../helpers.php';

setCorsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET')
    responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);

$db     = getDB();
$where  = ['1=1'];
$params = [];

if (!empty($_GET['mes']) && !empty($_GET['ano'])) {
    $where[]  = 'MONTH(cm.data_evento) = ?';
    $where[]  = 'YEAR(cm.data_evento)  = ?';
    $params[] = (int)$_GET['mes'];
    $params[] = (int)$_GET['ano'];
}

if (!empty($_GET['curso_id'])) {
    $where[]  = 'cm.curso_id = ?';
    $params[] = (int)$_GET['curso_id'];
}

$stmt = $db->prepare(
    "SELECT
        cm.id,
        cm.titulo,
        cm.mensagem,
        DATE_FORMAT(cm.data_evento, '%Y-%m-%d') AS data,
        cm.tipo,
        u.nome   AS professor_nome,
        c.nome   AS curso_nome
     FROM comunicados cm
     JOIN usuarios u   ON u.id  = cm.professor_id
     LEFT JOIN cursos c ON c.id = cm.curso_id
     WHERE " . implode(' AND ', $where) . "
     ORDER BY cm.data_evento ASC"
);
$stmt->execute($params);
responder($stmt->fetchAll());
