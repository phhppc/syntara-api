<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

$routes = [
    'auth/login'        => __DIR__ . '/auth/login.php',
    'usuarios/senha' => __DIR__ . '/usuarios/senha.php',
    'auth/registrar'    => __DIR__ . '/auth/registrar.php',
    'usuarios/perfil'   => __DIR__ . '/usuarios/perfil.php',
    'professores'       => __DIR__ . '/professores/index.php',
    'cursos'            => __DIR__ . '/cursos/index.php',
    'aulas'             => __DIR__ . '/aulas/index.php',
    'comunicados'       => __DIR__ . '/comunicados/index.php',
    'avaliacoes/minhas' => __DIR__ . '/avaliacoes/index.php',
    'avaliacoes'        => __DIR__ . '/avaliacoes/index.php',
    'denuncias'         => __DIR__ . '/denuncias/index.php',
];

// Rota de diagnóstico
if ($uri === 'ping') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'uri' => $uri,
        'request_uri' => $_SERVER['REQUEST_URI'],
        'rotas_disponiveis' => array_keys($routes)
    ]);
    exit;
}

if (isset($routes[$uri])) {
    require $routes[$uri];
} else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'mensagem' => 'Rota não encontrada',
        'uri_recebida' => $uri,
        'request_uri' => $_SERVER['REQUEST_URI']
    ]);
}
