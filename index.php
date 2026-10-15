<?php
session_start();
$env = parse_ini_file(".env");
foreach ($env as $key => $value) {
    putenv("$key=$value");
}

require_once("db.php");
require_once ("controllers/mainController.php");

if (isset($_GET['c']) && $_GET['c'] === 'order') {
    require_once("controllers/orderController.php");
    exit;
}

$controller = new MainController();

$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch ($action) {
    case 'order':
        require_once("controllers/orderController.php");
        break;
    case 'index':
        $controller->index();
        break;
    case 'login':
        $controller->login();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'register':
        $controller->register();
        break;
    case 'productos':
        $controller->productos();
        break;
    case 'panel':
        $controller->panel();
        break;
    case 'gestion':
        $controller->gestionProductos();
        break;
    case 'ver':
        $controller->verProducto($_GET['id'] ?? 0);
        break;
    case 'comprar':
        $controller->comprar();
        break;
    case 'confirmacion':
        $controller->confirmacion($_GET['id'] ?? 0);
        break;
    default:
        $controller->index();
        break;
}
?>