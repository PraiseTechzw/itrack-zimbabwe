<?php

class Router
{
    public static function route(string $uri): array
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = rtrim($path, '/');

        if ($path === '' || $path === '/') {
            return ['controller' => 'DashboardController', 'action' => 'index'];
        }

        $segments = explode('/', trim($path, '/'));
        if (count($segments) >= 2 && $segments[0] === 'api') {
            return ['controller' => ucfirst($segments[1]) . 'Controller', 'action' => $segments[2] ?? 'index'];
        }

        if (count($segments) >= 2) {
            return ['controller' => ucfirst($segments[0]) . 'Controller', 'action' => $segments[1]];
        }

        return ['controller' => 'DashboardController', 'action' => 'index'];
    }
}
