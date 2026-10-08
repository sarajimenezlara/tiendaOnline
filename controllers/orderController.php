<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/ProductRepository.php';
require_once __DIR__ . '/../models/OrderRepository.php';
require_once __DIR__ . '/../models/OrderLineRepository.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$productRepo = new ProductRepository();
$orderRepo = new OrderRepository();
$orderLineRepo = new OrderLineRepository();

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Función auxiliar para obtener el pedido en estado 'pendiente' (carrito) o crearlo si no existe
function getOrCreateCarrito(OrderRepository $orderRepo, int $userId): ?Pedido {
    $pedidos = $orderRepo->getByUser($userId);
    foreach ($pedidos as $p) {
        if ($p->getEstado() === 'pendiente') {
            return $p;
        }
    }
    $nuevoId = $orderRepo->create($userId, 0, 'pendiente');
    return $nuevoId ? $orderRepo->getById($nuevoId) : null;
}

if (isset($_GET['add'])) {
    if (isset($_POST['id']) && isset($_POST['quantity'])) {
        $productId = (int)$_POST['id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity > 0) {
            $product = $productRepo->getById($productId);
            $order = getOrCreateCarrito($orderRepo, $userId);

            if ($product && $order) {
                // Crear la línea de pedido
                $lineId = $orderLineRepo->create($order->getId(), $product->getId(), $quantity, $product->getPrecio());
                if ($lineId) {
                    // Actualizar el precio total del pedido
                    $newTotal = $order->getPrecioTotal() + ($product->getPrecio() * $quantity);
                    $orderRepo->updateTotal($order->getId(), $newTotal);

                    header('Location: index.php?c=order&show=1');
                    exit;
                }
            }
        }
    }

    header('Location: index.php');
    exit;
}

if (isset($_GET['checkout'])) {
    $order = null;
    $pedidos = $orderRepo->getByUser($userId);
    foreach ($pedidos as $p) {
        if ($p->getEstado() === 'pendiente') {
            $order = $p;
            break;
        }
    }
    if ($order) {
        $orderRepo->updateEstado($order->getId(), 'completado');
    }
    header('Location: index.php?c=order&show=1&success=1');
    exit;
}

if (isset($_GET['show'])) {
    $order = null;
    $pedidos = $orderRepo->getByUser($userId);
    foreach ($pedidos as $p) {
        if ($p->getEstado() === 'pendiente') {
            $order = $p;
            break;
        }
    }

    $lineas = [];
    if ($order) {
        $lineas = $orderLineRepo->getByPedido($order->getId());
    }

    require_once __DIR__ . '/../views/showOrderView.phtml';
    exit;
}