<?php
// GET /api/aulas
// GET /api/aulas?mes=X&ano=Y
// Retorna APENAS aulas dos cursos em que o aluno está matriculado

require_once __DIR__ . '/../helpers.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET')
    responder(['success' => false, 'mensagem' => 'Método não permitido.'], 405);

// Exige autenticação — pega o aluno_id do token JWT
$usuario = autenticar();
$aluno_id = (int)$usuario['id'];

$db     = getDB();
$where  = ['m.aluno_id = ?'];  // só cursos em que está matriculado
$params = [$aluno_id];

if (!empty($_GET['mes']) && !empty($_GET['ano'])) {
    $where[]  = 'MONTH(a.data_aula) = ?';
    $where[]  = 'YEAR(a.data_aula)  = ?';
    $params[] = (int)$_GET['mes'];
    $params[] = (int)$_GET['ano'];
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
     JOIN cursos c    ON c.id  = a.curso_id
     JOIN usuarios u  ON u.id  = c.professor_id
     JOIN matriculas m ON m.curso_id = a.curso_id
     WHERE " . implode(' AND ', $where) . "
       AND m.status = 'ativo'
     ORDER BY a.data_aula ASC"
);
$stmt->execute($params);
responder($stmt->fetchAll());
