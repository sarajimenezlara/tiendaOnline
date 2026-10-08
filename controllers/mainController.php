<?php
require_once 'views/mainView.phtml';
require_once 'models/Product.php';
require_once 'models/Linea_pedido.php';
require_once 'models/Pedido.php';
require_once 'models/User.php';
class MainController {
    
    public function index() {
        $products = Product::getAll();
        require_once 'views/mainView.phtml';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user = User::login($_POST['nombre'], $_POST['contraseña']);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                header('Location: index.php?action=index');
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
                $q = "INSERT INTO products VALUES(NULL,'" . $_POST['name'] . "','" . $_POST['description'] . "'," . $_POST['stock'] . "," . $_POST['price'] . ")";
                $pdo->query($q);
                header('Location: index.php?action=productos');
                exit();
            } else {
                echo "Faltan datos para agregar el producto.";
                exit();
            }
        }
        $products = Product::getAll();
        require_once 'views/productosView.phtml';
    }

    public function verProducto($id) {
        $product = Product::getById($id);
        require_once 'views/verProductoView.phtml';
    }

    public function comprar() {
        $pedido_id = Pedido::create($_SESSION['user_id']);
        header('Location: index.php?action=confirmacion&id=' . $pedido_id);
    }

    public function confirmacion($pedido_id) {
        $pedido = Pedido::getById($pedido_id);
        require_once 'views/confirmacionView.phtml';
    }
    public function logout() {
        session_destroy();
        header('Location: index.php?action=index');
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = User::register(
                $_POST['nombre'],
                '',
                $_POST['contraseña']
            );
            
            if ($result) {
                $_SESSION['user_id'] = $result;
                $_SESSION['user_nombre'] = $_POST['nombre'];
                header('Location: index.php?action=index');
            } else {
                echo "Error: el nombre de usuario ya existe";
            }
        }
        require_once 'views/registerView.phtml';
    }
}
?>
