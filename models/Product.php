<?php

require_once __DIR__ . '/../db.php';

class Producto
{
    private int $id;
    private string $nombre;
    private string $categoria;
    private int $stock;
    private float $precio;
    private ?string $descripcion;

    public function __construct(
        int $id = 0,
        string $nombre = '',
        string $categoria = '',
        int $stock = 0,
        float $precio = 0,
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

class Product
{
    private static function repo(): ProductRepository
    {
        require_once __DIR__ . '/ProductRepository.php';
        return new ProductRepository();
    }

    /** @return Producto[] */
    public static function getAll(): array
    {
        return self::repo()->getAll();
    }

    public static function getById($id): ?Producto
    {
        return self::repo()->getById((int)$id);
    }

    /** @return Producto[] Alias inglés */
    public static function getByCategory($categoria): array
    {
        return self::repo()->getByCategoria((string)$categoria);
    }

    /** @return Producto[] Alias español */
    public static function getByCategoria($categoria): array
    {
        return self::repo()->getByCategoria((string)$categoria);
    }

    /** @return Producto[] */
    public static function search($query): array
    {
        $conn = db::connect();
        $search = '%' . (string)$query . '%';
        $stmt = $conn->prepare('SELECT * FROM producto WHERE nombre LIKE ? OR descripcion LIKE ?');
        if (!$stmt) {
            $conn->close();
            return [];
        }
        $stmt->bind_param('ss', $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = new Producto(
                    (int)$row['id'],
                    $row['nombre'],
                    $row['categoria'],
                    (int)$row['stock'],
                    (float)$row['precio'],
                    $row['descripcion'] ?? null
                );
            }
        }
        $stmt->close();
        $conn->close();
        return $products;
    }

    public static function create(Producto $producto): int
    {
        return self::repo()->create($producto);
    }
}
