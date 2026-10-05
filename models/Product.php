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
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $products;
    }

    public static function getById($id) {
        $conectar = db::connect();
        $sql = $conectar->prepare("SELECT * FROM producto WHERE id = :id");
        $sql->bindParam(':id', $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }
    public static function getByCategory($categoria) {
        $conectar = db::connect();
        $sql = $conectar->prepare("SELECT * FROM producto WHERE categoria = :categoria");
        $sql->bindParam(':categoria', $categoria, PDO::PARAM_STR);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function search($query) {
        $conectar = db::connect();
        $sql = $conectar->prepare("SELECT * FROM producto WHERE nombre LIKE :query OR descripcion LIKE :query");
        $sql->bindParam(':query', $query, PDO::PARAM_STR);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>