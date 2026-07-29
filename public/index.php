<?php
session_start();
require_once dirname(__DIR__) . '/app/core/Router.php';

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$controllerKey = $_GET['controller'] ?? null;
$actionKey = $_GET['action'] ?? null;

$route = $controllerKey ? [
    'controller' => ucfirst($controllerKey) . 'Controller',
    'action' => $actionKey ?? 'index',
] : Router::route($uri);

$controllerName = $route['controller'];
$actionName = $route['action'];

$controllerFile = dirname(__DIR__) . '/app/controllers/' . $controllerName . '.php';
if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo 'Controller not found';
    exit;
}

require_once $controllerFile;
$controller = new $controllerName();
if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    echo 'Action not found';
    exit;
}

$controller->$actionName();
