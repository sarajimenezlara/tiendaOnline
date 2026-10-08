<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/ProductRepository.php';
require_once __DIR__ . '/../models/UserRepository.php';
require_once __DIR__ . '/../models/OrderRepository.php';
require_once __DIR__ . '/../models/OrderLineRepository.php';

class MainController {
    private ProductRepository $productRepo;
    private UserRepository $userRepo;
    private OrderRepository $orderRepo;

    public function __construct() {
        $this->productRepo = new ProductRepository();
        $this->userRepo = new UserRepository();
        $this->orderRepo = new OrderRepository();
    }
    
    public function index() {
        $products = $this->productRepo->getAll();
        require_once 'views/mainView.phtml';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userRow = $this->userRepo->getUserByUsername($_POST['nombre']);
            if ($userRow && password_verify($_POST['contraseña'], $userRow['password'])) {
                $_SESSION['user_id'] = $userRow['id'];
                $_SESSION['user_nombre'] = $userRow['nombre'];
                header('Location: index.php?action=index');
                exit();
            } else {
                echo "Nombre o contraseña incorrectos";
            }
        }
        require_once 'views/loginView.phtml';
    }

    public function productos() {
        if (isset($_GET['new'])) {
            require_once 'views/newProduct.phtml';
            return;
        }
        if (isset($_GET['add'])) {
            if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['stock']) && isset($_POST['price'])) {
                $producto = new Producto(
                    0,
                    $_POST['name'],
                    $_POST['categoria'] ?? 'General',
                    (int)$_POST['stock'],
                    (float)$_POST['price'],
                    $_POST['description']
                );
                $this->productRepo->create($producto);
                header('Location: index.php?action=productos');
                exit();
            } else {
                echo "Faltan datos para agregar el producto.";
                exit();
            }
        }
        $products = $this->productRepo->getAll();
        require_once 'views/productosView.phtml';
    }

    public function verProducto($id) {
        $product = $this->productRepo->getById((int)$id);
        require_once 'views/verProductoView.phtml';
    }

    public function comprar() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        $pedido_id = $this->orderRepo->create((int)$_SESSION['user_id'], 0.0, 'pendiente');
        header('Location: index.php?action=confirmacion&id=' . $pedido_id);
        exit();
    }

    public function confirmacion($pedido_id) {
        $pedido = $this->orderRepo->getById((int)$pedido_id);
        require_once 'views/confirmacionView.phtml';
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?action=index');
        exit();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);
            $contraseña = $_POST['contraseña'];
            $apellidos = $_POST['apellidos'] ?? '';
            $correo = $_POST['correo'] ?? ($nombre . '@local.test');

            // Comprobar si el usuario ya existe usando UserRepository
            if ($this->userRepo->getUserByUsername($nombre) || $this->userRepo->getByCorreo($correo)) {
                echo "Error: el nombre de usuario o correo ya existe";
            } else {
                $user = new User(0, $nombre, $apellidos, $correo, $contraseña);
                $nuevoId = $this->userRepo->create($user);
                if ($nuevoId) {
                    $_SESSION['user_id'] = $nuevoId;
                    $_SESSION['user_nombre'] = $nombre;
                    header('Location: index.php?action=index');
                    exit();
                } else {
                    echo "Error al registrar el usuario.";
                }
            }
        }
        require_once 'views/registerView.phtml';
    }
}
?>
