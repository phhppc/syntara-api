<?php
// GET /api/aulas
// GET /api/aulas?mes=X&ano=Y
// GET /api/aulas?curso_id=X
require_once __DIR__ . '/../helpers.php';

setCorsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET')
    responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);

$db     = getDB();
$where  = ['1=1'];
$params = [];

if (!empty($_GET['mes']) && !empty($_GET['ano'])) {
    $where[]  = 'MONTH(a.data_aula) = ?';
    $where[]  = 'YEAR(a.data_aula)  = ?';
    $params[] = (int)$_GET['mes'];
    $params[] = (int)$_GET['ano'];
}

if (!empty($_GET['curso_id'])) {
    $where[]  = 'a.curso_id = ?';
    $params[] = (int)$_GET['curso_id'];
}

$stmt = $db->prepare(
    "SELECT
        a.id,
        a.titulo,
        a.conteudo,
        DATE_FORMAT(a.data_aula, '%Y-%m-%d') AS data,
        c.nome  AS curso_nome,
        u.nome  AS professor_nome
     FROM aulas a
     JOIN cursos c   ON c.id  = a.curso_id
     JOIN usuarios u ON u.id  = c.professor_id
     WHERE " . implode(' AND ', $where) . "
     ORDER BY a.data_aula ASC"
);
$stmt->execute($params);
responder($stmt->fetchAll());
