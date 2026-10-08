<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carga .env robusta: ignora comentarios, recorta espacios, alimenta putenv + $_ENV
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if (is_array($env)) {
        foreach ($env as $key => $value) {
            $key = trim((string)$key);
            $value = trim(trim((string)$value), "\"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}
// Compatibilidad DB_PASS / DB_PASSWORD
if (empty(getenv('DB_PASS')) && !empty(getenv('DB_PASSWORD'))) {
    putenv('DB_PASS=' . getenv('DB_PASSWORD'));
    $_ENV['DB_PASS'] = getenv('DB_PASSWORD');
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/mainController.php';
require_once __DIR__ . '/controllers/userController.php';

$controller = new MainController();
$userController = new UserController();

// POST de auth antes del router GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $userController->doRegister();
    } elseif (isset($_POST['login']) || isset($_POST['doLogin'])) {
        $userController->doLogin();
    } elseif (isset($_POST['doRegister'])) {
        $userController->doRegister();
    } elseif (isset($_POST['nombre'], $_POST['password']) && isset($_GET['action']) && $_GET['action'] === 'register') {
        $userController->doRegister();
    } elseif (isset($_POST['nombre'], $_POST['password'])) {
        // Por defecto los formularios de login hacen POST a index.php
        // Si incluye apellidos/correo es registro, si no es login
        if (!empty($_POST['correo']) || !empty($_POST['apellidos'])) {
            $userController->doRegister();
        } else {
            $userController->doLogin();
        }
    }
}

$action = $_GET['action'] ?? 'index';

try {
    switch ($action) {
        case 'index':
            $controller->index();
            break;
        case 'login':
            $controller->login();
            break;
        case 'register':
            $controller->register();
            break;
        case 'logout':
            $controller->logout();
            break;
        case 'productos':
            $controller->productos();
            break;
        case 'ver':
            if (empty($_GET['id'])) {
                http_response_code(400);
                echo 'Falta id';
                break;
            }
            $controller->verProducto($_GET['id']);
            break;
        case 'comprar':
            $controller->comprar();
            break;
        case 'confirmacion':
            if (empty($_GET['id'])) {
                http_response_code(400);
                echo 'Falta id de pedido';
                break;
            }
            $controller->confirmacion($_GET['id']);
            break;
        default:
            $controller->index();
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
