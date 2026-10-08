<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Product.php';

class ProductRepository
{
    private static function mapRow(array $row): Producto
    {
        return new Producto(
            (int)$row['id'],
            $row['nombre'],
            $row['categoria'],
            (int)$row['stock'],
            (float)$row['precio'],
            $row['descripcion'] ?? null
        );
    }

    /** @return Producto[] */
    public function getAll(): array
    {
        $conn = db::connect();
        $result = $conn->query('SELECT * FROM producto ORDER BY id ASC');
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = self::mapRow($row);
            }
            $result->free();
        }
        $conn->close();
        return $products;
    }

    public function getById(int $id): ?Producto
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM producto WHERE id = ?');
        if (!$stmt) {
            $conn->close();
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->close();
        return $row ? self::mapRow($row) : null;
    }

    /** @return Producto[] */
    public function getByCategoria(string $categoria): array
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM producto WHERE categoria = ? ORDER BY id ASC');
        if (!$stmt) {
            $conn->close();
            return [];
        }
        $stmt->bind_param('s', $categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = self::mapRow($row);
            }
        }
        $stmt->close();
        $conn->close();
        return $products;
    }

    public function create(Producto $producto): int
    {
        $conn = db::connect();
        $stmt = $conn->prepare(
            'INSERT INTO producto (nombre, categoria, stock, precio, descripcion) VALUES (?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            $conn->close();
            throw new RuntimeException('Prepare failed: ' . $conn->error);
        }
        $nombre = $producto->getNombre();
        $categoria = $producto->getCategoria();
        $stock = $producto->getStock();
        $precio = $producto->getPrecio();
        $descripcion = $producto->getDescripcion();
        $stmt->bind_param('ssids', $nombre, $categoria, $stock, $precio, $descripcion);
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            $conn->close();
            throw new RuntimeException('Execute failed: ' . $err);
        }
        $id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        return $id;
    }

    public function update(Producto $producto): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare(
            'UPDATE producto SET nombre = ?, categoria = ?, stock = ?, precio = ?, descripcion = ? WHERE id = ?'
        );
        if (!$stmt) {
            $conn->close();
            return false;
        }
        $nombre = $producto->getNombre();
        $categoria = $producto->getCategoria();
        $stock = $producto->getStock();
        $precio = $producto->getPrecio();
        $descripcion = $producto->getDescripcion();
        $id = $producto->getId();
        $stmt->bind_param('ssidsi', $nombre, $categoria, $stock, $precio, $descripcion, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public function updateStock(int $id, int $stock): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare('UPDATE producto SET stock = ? WHERE id = ?');
        if (!$stmt) {
            $conn->close();
            return false;
        }
        $stmt->bind_param('ii', $stock, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public function delete(int $id): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare('DELETE FROM producto WHERE id = ?');
        if (!$stmt) {
            $conn->close();
            return false;
        }
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }
}

if (!class_exists('ProductoRepository', false)) {
    class_alias('ProductRepository', 'ProductoRepository');
}
