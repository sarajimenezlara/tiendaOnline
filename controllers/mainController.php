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
    private OrderLineRepository $orderLineRepo;

    public function __construct() {
        $this->productRepo = new ProductRepository();
        $this->userRepo = new UserRepository();
        $this->orderRepo = new OrderRepository();
        $this->orderLineRepo = new OrderLineRepository();
    }
    
    public function index() {
        $products = $this->productRepo->getAll();
        require_once 'views/mainView.phtml';
    }

    public function login() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userRow = $this->userRepo->getUserByUsername(trim($_POST['nombre'] ?? ''));
            if ($userRow && password_verify($_POST['contraseña'] ?? '', $userRow['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $userRow['id'];
                $_SESSION['user_nombre'] = $userRow['nombre'];
                $_SESSION['user_rol'] = $userRow['rol'] ?? 'usuario';
                header('Location: index.php?action=index');
                exit();
            } else {
                $error = "Nombre o contraseña incorrectos";
            }
        }
        require_once 'views/loginView.phtml';
    }

    // Redirige al login si no hay sesión iniciada
    private function soloLogueado() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
    }

    // Redirige al inicio si el usuario no es admin
    private function soloAdmin() {
        if (($_SESSION['user_rol'] ?? '') !== 'admin') {
            header('Location: index.php?action=index');
            exit();
        }
    }

    public function productos() {
        if (isset($_GET['new'])) {
            $this->soloAdmin();
            $error = null;
            require_once 'views/newProduct.phtml';
            return;
        }
        if (isset($_GET['edit'])) {
            $this->soloAdmin();
            $error  = null;
            $product = $this->productRepo->getById((int)($_GET['id'] ?? 0));
            if (!$product) {
                header('Location: index.php?action=gestion');
                exit();
            }
            require_once 'views/editProduct.phtml';
            return;
        }
        if (isset($_GET['delete'])) {
            $this->soloAdmin();
            $productoId = (int)$_GET['delete'];
            try {
                // Eliminar primero las líneas de pedido que referencian al producto
                $lineasAfectadas = $this->orderLineRepo->getByProducto($productoId);
                $pedidosAfectados = [];
                foreach ($lineasAfectadas as $linea) {
                    $pedidosAfectados[$linea->getPedidoId()] = true;
                }
                $this->orderLineRepo->deleteByProducto($productoId);
                $this->productRepo->delete($productoId);
                // Recalcular totales de los pedidos afectados
                foreach (array_keys($pedidosAfectados) as $pedidoId) {
                    $nuevoTotal = $this->orderLineRepo->calcTotal($pedidoId);
                    $this->orderRepo->updateTotal($pedidoId, $nuevoTotal);
                }
                header('Location: index.php?action=gestion&ok=eliminado');
            } catch (\Throwable $e) {
                header('Location: index.php?action=gestion&error=noeliminar');
            }
            exit();
        }
        if (isset($_GET['update'])) {
            $this->soloAdmin();
            $id = (int)($_POST['id'] ?? 0);
            $product = $this->productRepo->getById($id);
            $error  = null;
            if (!$product) {
                header('Location: index.php?action=gestion');
                exit();
            }

            $nombre     = trim($_POST['name'] ?? '');
            $descripcion = trim($_POST['description'] ?? '');
            $categoria  = $_POST['categoria'] ?? '';
            $stock      = filter_var($_POST['stock'] ?? '', FILTER_VALIDATE_INT);
            $precio     = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);

            if ($nombre === '' || $descripcion === '') {
                $error = "Faltan datos para editar el producto.";
            } elseif (!array_key_exists($categoria, Producto::CATEGORIAS)) {
                $error = "Categoría no válida.";
            } elseif ($stock === false || $stock < 0) {
                $error = "El stock debe ser un número entero mayor o igual que 0.";
            } elseif ($precio === false || $precio < 0) {
                $error = "El precio debe ser un número mayor o igual que 0.";
            }

            if ($error) {
                require_once 'views/editProduct.phtml';
                return;
            }

            $producto = new Producto($id, $nombre, $categoria, $stock, $precio, $descripcion);
            $this->productRepo->update($producto);
            header('Location: index.php?action=gestion&ok=editado');
            exit();
        }
        if (isset($_GET['add'])) {
            $this->soloAdmin();
            $nombre = trim($_POST['name'] ?? '');
            $descripcion = trim($_POST['description'] ?? '');
            $categoria = $_POST['categoria'] ?? '';
            $stock = filter_var($_POST['stock'] ?? '', FILTER_VALIDATE_INT);
            $precio = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);

            $error = null;
            if ($nombre === '' || $descripcion === '') {
                $error = "Faltan datos para agregar el producto.";
            } elseif (!array_key_exists($categoria, Producto::CATEGORIAS)) {
                $error = "Categoría no válida.";
            } elseif ($stock === false || $stock < 0) {
                $error = "El stock debe ser un número entero mayor o igual que 0.";
            } elseif ($precio === false || $precio < 0) {
                $error = "El precio debe ser un número mayor o igual que 0.";
            }

            if ($error) {
                require_once 'views/newProduct.phtml';
                return;
            }

            $producto = new Producto(0, $nombre, $categoria, $stock, $precio, $descripcion);
            $this->productRepo->create($producto);
            header('Location: index.php?action=gestion&ok=creado');
            exit();
        }
        $categoria = $_GET['categoria'] ?? null;
        if ($categoria && array_key_exists($categoria, Producto::CATEGORIAS)) {
            $products = $this->productRepo->getByCategoria($categoria);
        } else {
            $categoria = null; // "Ver todo"
            $products = $this->productRepo->getAll();
        }
        require_once 'views/productosView.phtml';
    }

    // Listado de productos para el admin, con editar y eliminar
    public function gestionProductos() {
        $this->soloAdmin();
        $products = $this->productRepo->getAll();
        require_once 'views/gestionProductosView.phtml';
    }

    public function verProducto($id) {
        $product = $this->productRepo->getById((int)$id);
        require_once 'views/verProductoView.phtml';
    }

    public function panel() {
        $this->soloAdmin();
        $pedidos = $this->orderRepo->getAll();
        $userRepo = $this->userRepo;
        $orderLineRepo = $this->orderLineRepo;
        $productRepo = $this->productRepo;
        require_once 'views/panelView.phtml';
    }

    // Antes creaba un pedido vacío; ahora finaliza la compra del carrito
    public function comprar() {
        $this->soloLogueado();
        header('Location: index.php?c=order&checkout=1');
        exit();
    }

    public function confirmacion($pedido_id) {
        $this->soloLogueado();
        $pedido = $this->orderRepo->getById((int)$pedido_id);
        // Solo el dueño del pedido o un admin pueden verlo
        if ($pedido && $pedido->getUserId() !== (int)$_SESSION['user_id'] && ($_SESSION['user_rol'] ?? '') !== 'admin') {
            $pedido = null;
        }
        $lineas = $pedido ? $this->orderLineRepo->getByPedido($pedido->getId()) : [];
        $productRepo = $this->productRepo;
        require_once 'views/confirmacionView.phtml';
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?action=index');
        exit();
    }

    public function register() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $contraseña = $_POST['contraseña'] ?? '';
            $apellidos = $_POST['apellidos'] ?? '';
            $correo = $_POST['correo'] ?? ($nombre . '@local.test');

            if ($nombre === '' || $contraseña === '') {
                $error = "El nombre y la contraseña son obligatorios.";
            } elseif ($this->userRepo->getUserByUsername($nombre) || $this->userRepo->getByCorreo($correo)) {
                // Comprobar si el usuario ya existe usando UserRepository
                $error = "El nombre de usuario o correo ya existe.";
            } else {
                $user = new User(0, $nombre, $apellidos, $correo, $contraseña);
                $nuevoId = $this->userRepo->create($user);
                if ($nuevoId) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $nuevoId;
                    $_SESSION['user_nombre'] = $nombre;
                    $_SESSION['user_rol'] = 'usuario';
                    header('Location: index.php?action=index');
                    exit();
                } else {
                    $error = "Error al registrar el usuario.";
                }
            }
        }
        require_once 'views/registerView.phtml';
    }
}
?>
