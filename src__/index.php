<?php
require_once __DIR__ . '/controller/homeController.php';
require_once __DIR__ . '/controller/registerController.php';
require_once __DIR__ . '/controller/loginController.php';
require_once __DIR__ . '/controller/profileController.php';
require_once __DIR__ . '/controller/postDetailsController.php';
require_once __DIR__ . '/controller/dashboardController.php';
require_once __DIR__ . '/controller/allPostController.php';
require_once __DIR__ . '/controller/researchController.php';
require_once __DIR__ . '/controller/adviceController.php';

require_once __DIR__ . '/database/createDatabase.php';

// Routes definition
$routes = [
    '/' => ['controller' => 'home\homeController', 'method' => 'home'],
    '/register' => ['controller' => 'register\registerController', 'method' => 'register'],
    '/login' => ['controller' => 'login\loginController', 'method' => 'login'],
    '/dashboard' => ['controller' => 'dashboard\dashboardController', 'method' => 'dashboard'],
    '/allPost' => ['controller' => 'allPost\allPostController', 'method' => 'allPost'],
    '/research' => ['controller' => 'research\researchController', 'method' => 'research'],
    '/advice' => ['controller' => 'advice\adviceController', 'method' => 'advice'],
];

// Extract the path from the request URI
$requestParts = explode('?', $_SERVER['REQUEST_URI'], 2);
$path = $requestParts[0];

// Define the base URI for the application
$baseUri = '/ifadev/src/index.php';
$path = str_replace($baseUri, '', $path); // Remove the base URI

// Handle the route
if (array_key_exists($path, $routes)) {
    $controllerName = $routes[$path]['controller'];
    $methodName = $routes[$path]['method'];

    // Instantiate the controller and call the method
    $controller = new $controllerName();
    $params = isset($requestParts[1]) ? $requestParts[1] : '';
    $controller->$methodName();
} else {
    // Handle dynamic routes
    if (preg_match('/^\/profile-(\d+)$/', $path, $matches)) {
        $controller = new profile\profileController();
        $controller->profile($matches[1]);
    } elseif (preg_match('/^\/postDetails-(\d+)$/', $path, $matches)) {
        $controller = new postDetails\postDetailsController();
        $controller->post($matches[1]);
    } else {
        // Return a 404 response for unknown routes
        http_response_code(404);
        echo "Page not found";
    }
}