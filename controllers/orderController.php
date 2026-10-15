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

// Función auxiliar para obtener el pedido en estado 'pendiente' (carrito)
function getCarrito(OrderRepository $orderRepo, int $userId): ?Pedido {
    foreach ($orderRepo->getByUser($userId) as $p) {
        if ($p->getEstado() === 'pendiente') {
            return $p;
        }
    }
    return null;
}

// Igual que getCarrito, pero si no existe lo crea
function getOrCreateCarrito(OrderRepository $orderRepo, int $userId): ?Pedido {
    $carrito = getCarrito($orderRepo, $userId);
    if ($carrito) {
        return $carrito;
    }
    $nuevoId = $orderRepo->create($userId, 0, 'pendiente');
    return $nuevoId ? $orderRepo->getById($nuevoId) : null;
}

if (isset($_GET['add'])) {
    $productId = (int)($_POST['id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $product = $productRepo->getById($productId);

    if (!$product || $quantity <= 0) {
        header('Location: index.php?action=productos');
        exit;
    }

    $order = getOrCreateCarrito($orderRepo, $userId);
    if (!$order) {
        header('Location: index.php?action=productos');
        exit;
    }

    // Si el producto ya está en el carrito, se suma la cantidad en la misma línea
    $lineaExistente = null;
    foreach ($orderLineRepo->getByPedido($order->getId()) as $linea) {
        if ($linea->getProductoId() === $product->getId()) {
            $lineaExistente = $linea;
            break;
        }
    }
    $cantidadTotal = $quantity + ($lineaExistente ? $lineaExistente->getCantidad() : 0);

    // No se puede pedir más de lo que hay en stock
    if ($cantidadTotal > $product->getStock()) {
        header('Location: index.php?c=order&show=1&error=stock');
        exit;
    }

    if ($lineaExistente) {
        $orderLineRepo->updateCantidad($lineaExistente->getId(), $cantidadTotal);
    } else {
        $orderLineRepo->create($order->getId(), $product->getId(), $quantity, $product->getPrecio());
    }
    $orderRepo->updateTotal($order->getId(), $orderLineRepo->calcTotal($order->getId()));

    header('Location: index.php?c=order&show=1');
    exit;
}

if (isset($_GET['checkout'])) {
    $order = getCarrito($orderRepo, $userId);
    $lineas = $order ? $orderLineRepo->getByPedido($order->getId()) : [];

    if (empty($lineas)) {
        header('Location: index.php?c=order&show=1');
        exit;
    }

    // Comprobar que sigue habiendo stock de todo antes de cobrar nada
    foreach ($lineas as $linea) {
        $prod = $productRepo->getById($linea->getProductoId());
        if (!$prod || $prod->getStock() < $linea->getCantidad()) {
            header('Location: index.php?c=order&show=1&error=stock');
            exit;
        }
    }

    // Descontar el stock y cerrar el pedido
    foreach ($lineas as $linea) {
        $prod = $productRepo->getById($linea->getProductoId());
        $productRepo->updateStock($prod->getId(), $prod->getStock() - $linea->getCantidad());
    }
    $orderRepo->updateTotal($order->getId(), $orderLineRepo->calcTotal($order->getId()));
    $orderRepo->updateEstado($order->getId(), 'completado');

    header('Location: index.php?action=confirmacion&id=' . $order->getId());
    exit;
}

if (isset($_GET['show'])) {
    $order = getCarrito($orderRepo, $userId);
    $lineas = $order ? $orderLineRepo->getByPedido($order->getId()) : [];

    require_once __DIR__ . '/../views/showOrderView.phtml';
    exit;
}

header('Location: index.php?c=order&show=1');
exit;
