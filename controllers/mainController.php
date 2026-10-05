<?php
require_once 'views/mainView.phtml';
require_once 'models/Product.php';
require_once 'models/Detalle_pedido.php';
require_once 'models/Pedido.php';
require_once 'models/User.php';

class MainController {

    public function index() {
        $products = Product::getAll();
        require_once 'views/mainView.phtml';
    }

    public function login() {
        require_once 'views/loginView.phtml';
        require_once 'models/User.php';
    }

    public function productos() {
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
    public function register() {
        require_once 'views/registerView.phtml';
    }
}
?>
