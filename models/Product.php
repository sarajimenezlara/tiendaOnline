<?php
class Product {
    private $id;
    private $nombre;
    private $descripcion;
    private $precio;
    private $categoria;
    private $stock;

    public static function getAll() {
        $conectar = db::connect();
        $sql = "SELECT * FROM producto";
        $result = $conectar->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }

    public static function getById($id) {
        $conectar = db::connect();
        $stmt = $conectar->prepare("SELECT * FROM producto WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public static function getByCategory($categoria) {
        $conectar = db::connect();
        $stmt = $conectar->prepare("SELECT * FROM producto WHERE categoria = ?");
        $stmt->bind_param("s", $categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }

    public static function search($query) {
        $conectar = db::connect();
        $search = "%$query%";
        $stmt = $conectar->prepare("SELECT * FROM producto WHERE nombre LIKE ? OR descripcion LIKE ?");
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }
}
?>
