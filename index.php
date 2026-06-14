<?php
// Router principal da API Syntara
// Substitui o .htaccess para funcionar no Render

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Mapa de rotas
$routes = [
    'auth/login'        => __DIR__ . '/auth/login.php',
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

if (isset($routes[$uri])) {
    require $routes[$uri];
} else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'mensagem' => 'Rota não encontrada: ' . $uri]);
}
