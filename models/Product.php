<?php
class Producto
{
    private int $id;
    private string $nombre;
    private string $categoria;
    private int $stock;
    private float $precio;
    private ?string $descripcion;

    public function __construct(
        int $id,
        string $nombre,
        string $categoria,
        int $stock,
        float $precio,
        ?string $descripcion = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->categoria = $categoria;
        $this->stock = $stock;
        $this->precio = $precio;
        $this->descripcion = $descripcion;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function getCategoria(): string
    {
        return $this->categoria;
    }

    public function setCategoria(string $categoria): void
    {
        $this->categoria = $categoria;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function getPrecio(): float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): void
    {
        $this->precio = $precio;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }
}
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
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            $result->free();
        }
        $conectar->close();
        return $products;
    }

    public static function getById($id) {
        $conectar = db::connect();
        $stmt = $conectar->prepare("SELECT * FROM producto WHERE id = ?");
        if (!$stmt) {
            $conectar->close();
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conectar->close();
        return $row ?: null;
    }

    public static function getByCategory($categoria) {
        $conectar = db::connect();
        $stmt = $conectar->prepare("SELECT * FROM producto WHERE categoria = ?");
        if (!$stmt) {
            $conectar->close();
            return [];
        }
        $stmt->bind_param("s", $categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        $stmt->close();
        $conectar->close();
        return $products;
    }

    public static function search($query) {
        $conectar = db::connect();
        $search = "%$query%";
        $stmt = $conectar->prepare("SELECT * FROM producto WHERE nombre LIKE ? OR descripcion LIKE ?");
        if (!$stmt) {
            $conectar->close();
            return [];
        }
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        $stmt->close();
        $conectar->close();
        return $products;
    }
}
?>
