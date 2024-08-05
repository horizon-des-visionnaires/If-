<?php
// Inclusion des contrôleurs nécessaires pour gérer les différentes routes
require_once __DIR__ . '/controller/homeController.php';
require_once __DIR__ . '/controller/registerController.php';
require_once __DIR__ . '/controller/loginController.php';
require_once __DIR__ . '/controller/profileController.php';
require_once __DIR__ . '/controller/postDetailsController.php';
require_once __DIR__ . '/controller/dashboardController.php';
require_once __DIR__ . '/controller/allPostController.php';
require_once __DIR__ . '/controller/researchController.php';
require_once __DIR__ . '/controller/adviceController.php';
require_once __DIR__ . '/controller/verifyController.php';
require_once __DIR__ . '/controller/forgotPasswordController.php';
require_once __DIR__ . '/controller/resetPasswordController.php';
require_once __DIR__ . '/controller/conversationController.php';
require_once __DIR__ . '/controller/conversationChatController.php';

require_once __DIR__ . '/database/createDatabase.php'; // Inclusion du script de création de la base de données

// Définition des routes avec les contrôleurs et méthodes correspondantes
$routes = [
  '/' => ['controller' => 'home\homeController', 'method' => 'home'],
  '/register' => ['controller' => 'register\registerController', 'method' => 'register'],
  '/login' => ['controller' => 'login\loginController', 'method' => 'login'],
  '/dashboard' => ['controller' => 'dashboard\dashboardController', 'method' => 'dashboard'],
  '/allPost' => ['controller' => 'allPost\allPostController', 'method' => 'allPost'],
  '/research' => ['controller' => 'research\researchContoller', 'method' => 'research'],
  '/advice' => ['controller' => 'advice\adviceController', 'method' => 'advice'],
  '/verify' => ['controller' => 'verify\verifyController', 'method' => 'verify'],
  '/forgot-password' => ['controller' => 'forgotPassword\forgotPasswordController', 'method' => 'forgotPassword'],
  '/reset-password' => ['controller' => 'resetPassword\resetPasswordController', 'method' => 'resetPassword'],
  '/conversation' => ['controller' => 'conversation\conversationController', 'method' => 'conversation'],
  '/verify' => ['controller' => 'verify\verifyController', 'method' => 'verify'],
  '/forgot-password' => ['controller' => 'forgotPassword\forgotPasswordController', 'method' => 'forgotPassword'],
  '/reset-password' => ['controller' => 'resetPassword\resetPasswordController', 'method' => 'resetPassword'],
  '/conversation' => ['controller' => 'conversation\conversationController', 'method' => 'conversation'],
];

// Récupération du chemin de la requête
$requestParts = explode('?', $_SERVER['REQUEST_URI'], 2); // Sépare l'URL pour obtenir le chemin et les paramètres éventuels
$path = $requestParts[0]; // Récupère le chemin sans les paramètres

// Vérification si le chemin existe dans les routes définies
if (array_key_exists($path, $routes)) {
  $controllerName = $routes[$path]['controller']; // Nom du contrôleur
  $methodName = $routes[$path]['method']; // Nom de la méthode

  $controller = new $controllerName(); // Instanciation du contrôleur

  $params = isset($requestParts[1]) ? $requestParts[1] : ''; // Paramètres de la requête (s'ils existent)

  $controller->$methodName(); // Appel de la méthode correspondante
} else {
  // Gestion des routes dynamiques pour les profils, les détails des posts et les conversations de chat
  if (preg_match('/^\/profile-(\d+)$/', $path, $matches)) {
    $controller = new profile\profileController();
    $controller->profile($matches[1]); // Appel de la méthode avec l'ID du profil
  } else if (preg_match('/^\/postDetails-(\d+)$/', $path, $matches)) {
    $controller = new postDetails\postDetailsController();
    $controller->post($matches[1]); // Appel de la méthode avec l'ID du post
  } else if (preg_match('/^\/conversationChat-(\d+)$/', $path, $matches)) {
    $controller = new conversationChat\conversationChatController();
    $controller->conversationChat($matches[1]); // Appel de la méthode avec l'ID de la conversation de chat
  }
  else {
    // Retourne une erreur 404 si la route n'est pas trouvée
    http_response_code(404);
    echo "Page not found"; // Message d'erreur
  }
}
?>