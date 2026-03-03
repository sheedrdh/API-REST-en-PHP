<?php
declare(strict_types = 1)
;

// Autoriser les requêtes cross‑origin (CORS) – indispensable pour les apps tierces
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Répondre aux requêtes OPTIONS (pré‑flight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Toujours répondre en JSON
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/ProductController.php';

// Nettoyage de l’URL : /api/products/12 → ['products','12']
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uriSegments = explode('/', $uri);

// On retire les dossiers parents (ex: my-api) jusqu'à trouver 'api' ou 'products'
while (count($uriSegments) > 0 && $uriSegments[0] !== 'api' && $uriSegments[0] !== 'products') {
    array_shift($uriSegments);
}

// /api/ → on ignore le segment « api »
if (count($uriSegments) > 0 && $uriSegments[0] === 'api') {
    array_shift($uriSegments);
}

// Routeur très basique (pour un projet réel vous pouvez utiliser Slim, Lumen…)
$resource = $uriSegments[0] ?? '';
$id = isset($uriSegments[1]) ? (int)$uriSegments[1] : null;
$method = $_SERVER['REQUEST_METHOD'];

$controller = new ProductController();

switch ($resource) {
    case 'products':
        // Gestion du CRUD selon la méthode HTTP
        if ($method === 'GET') {
            if ($id)
                $controller->get($id);
            else
                $controller->list();
        }
        elseif ($method === 'POST') {
            $controller->create();
        }
        elseif ($method === 'PUT' && $id) {
            $controller->update($id);
        }
        elseif ($method === 'DELETE' && $id) {
            $controller->delete($id);
        }
        else {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Ressource introuvable']);
        break;
}
