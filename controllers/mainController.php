<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Linea_pedido.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ProductRepository.php';
require_once __DIR__ . '/../models/OrderRepository.php';
require_once __DIR__ . '/../models/OrderLineRepository.php';
require_once __DIR__ . '/../models/UserRepository.php';

class MainController {

    private function ensureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function view(string $file, array $data = []): void {
        extract($data);
        $path = __DIR__ . '/../views/' . $file;
        if (!file_exists($path)) {
            throw new RuntimeException('Vista no encontrada: ' . $file);
        }
        require $path;
    }

    public function index() {
        $repo = new ProductRepository();
        try {
            $products = $repo->getAll();
        } catch (Throwable $e) {
            $products = [];
            $dbError = $e->getMessage();
        }
        $this->view('mainView.phtml', ['products' => $products, 'dbError' => $dbError ?? null]);
    }

    public function login() {
        $this->ensureSession();
        $this->view('loginView.phtml');
    }

    public function register() {
        $this->ensureSession();
        $this->view('registerView.phtml');
    }

    public function productos() {
        $repo = new ProductRepository();
        try {
            $products = $repo->getAll();
        } catch (Throwable $e) {
            $products = [];
            $dbError = $e->getMessage();
        }
        $this->view('productosView.phtml', ['products' => $products, 'dbError' => $dbError ?? null]);
    }

    public function verProducto($id) {
        $id = (int)$id;
        if ($id <= 0) {
            http_response_code(404);
            echo 'Producto no válido';
            return;
        }
        $repo = new ProductRepository();
        try {
            $product = $repo->getById($id);
        } catch (Throwable $e) {
            $product = null;
            $dbError = $e->getMessage();
        }
        if (!$product) {
            http_response_code(404);
            echo 'Producto no encontrado';
            return;
        }
        $this->view('verProductoView.phtml', ['product' => $product, 'dbError' => $dbError ?? null]);
    }

    public function comprar() {
        $this->ensureSession();
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        try {
            $pedido_id = Pedido::create((int)$_SESSION['user_id']);
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'No se pudo crear el pedido: ' . htmlspecialchars($e->getMessage());
            return;
        }
        header('Location: index.php?action=confirmacion&id=' . $pedido_id);
        exit();
    }

    public function confirmacion($pedido_id) {
        $pedido_id = (int)$pedido_id;
        if ($pedido_id <= 0) {
            http_response_code(404);
            echo 'Pedido no válido';
            return;
        }
        try {
            $pedido = Pedido::getById($pedido_id);
            $lineRepo = new OrderLineRepository();
            $lineas = $lineRepo->getByPedido($pedido_id);
            $total = $lineRepo->calcTotal($pedido_id);
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Error al cargar el pedido: ' . htmlspecialchars($e->getMessage());
            return;
        }
        if (!$pedido) {
            http_response_code(404);
            echo 'Pedido no encontrado';
            return;
        }
        $this->view('confirmacionView.phtml', ['pedido' => $pedido, 'lineas' => $lineas, 'total' => $total]);
    }

    public function logout() {
        $this->ensureSession();
        session_destroy();
        header('Location: index.php');
        exit();
    }
}
