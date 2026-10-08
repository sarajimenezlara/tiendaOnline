<?php
if(isset($_GET['new'])){
    require_once('views/newProduct.phtml');
    exit();
}
if(isset($_GET['add'])){
    if(isset($_POST['name']) && isset($_POST['description']) && isset($_POST['stock']) && isset($_POST['price'])){
    $q = "INSERT INTO products VALUES(NULL,'" . $_POST['name'] . "','" . $_POST['description'] . "'," . $_POST['stock'] . "," . $_POST['price'] . ")";
    $result = $pdo->query($q);
    header('Location: index.php?action=productos');
    exit();
    } else {
        echo "Faltan datos para agregar el producto.";
        exit();
    }
}