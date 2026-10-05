<?php
require_once 'views/mainView.phtml';
require_once 'models/Carrito.php';
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
    public function productos(){
        $products = Product::getAll();
        require_once 'views/productosView.phtml';
    }
    public function verProducto($id) {
        $product = Product::getById($id);
        require_once 'views/verProductoView.phtml';
    }
    public function carrito() {
        $carrito = carrito::getCarrito();
        require_once 'views/carritoView.phtml';
    }
    public function agregarAlCarrito($id) {
        $product = Product::getById($id);
        carrito::agregarProducto($product);
        header('Location: index.php?controller=main&action=carrito');
    }
    public function eliminarDelCarrito($id) {
        carrito::eliminarProducto($id);
        header('Location: index.php?controller=main&action=carrito');
    }
    public function comprar() {
        $productos = carrito::getCarrito();
        $pedido = new Pedido();
        $pedido->setUserId($_SESSION['user_id']);
        $pedido->save();
        foreach ($productos as $producto) {
            $detalle = new Detalle_pedido();
            $detalle->setPedidoId($pedido->getId());
            $detalle->setProductoId($producto['id']);
            $detalle->setCantidad($producto['cantidad']);
            $detalle->save();
        }
    }
    public function confirmacion($pedidoId) {
        $pedido = Pedido::getById($pedidoId);
        require_once 'views/confirmacionView.phtml';
    }
    public function logout() {
        session_destroy();
        header('Location: index.php');
    }
}
?>
