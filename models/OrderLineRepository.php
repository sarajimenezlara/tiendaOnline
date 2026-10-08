<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Linea_pedido.php';

class OrderLineRepository
{
    private static function mapRow(array $row): LineaPedido
    {
        return new LineaPedido(
            (int)$row['id'],
            (int)$row['pedido_id'],
            (int)$row['producto_id'],
            (int)$row['cantidad'],
            (float)$row['precio_unitario']
        );
    }

    public function getById(int $id): ?LineaPedido
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM linea_pedido WHERE id = ?');
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

    /** @return LineaPedido[] */
    public function getByPedido(int $pedido_id): array
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM linea_pedido WHERE pedido_id = ? ORDER BY id ASC');
        if (!$stmt) {
            $conn->close();
            return [];
        }
        $stmt->bind_param('i', $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $lineas = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $lineas[] = self::mapRow($row);
            }
        }
        $stmt->close();
        $conn->close();
        return $lineas;
    }

    public function create(int $pedido_id, int $producto_id, int $cantidad, float $precio_unitario): int
    {
        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad debe ser > 0');
        }
        $conn = db::connect();
        $stmt = $conn->prepare(
            'INSERT INTO linea_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)'
        );
        if (!$stmt) {
            $conn->close();
            throw new RuntimeException('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('iiid', $pedido_id, $producto_id, $cantidad, $precio_unitario);
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

    public function updateCantidad(int $id, int $cantidad): bool
    {
        if ($cantidad <= 0) {
            return false;
        }
        $conn = db::connect();
        $stmt = $conn->prepare('UPDATE linea_pedido SET cantidad = ? WHERE id = ?');
        if (!$stmt) {
            $conn->close();
            return false;
        }
        $stmt->bind_param('ii', $cantidad, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public function delete(int $id): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare('DELETE FROM linea_pedido WHERE id = ?');
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

    public function deleteByPedido(int $pedido_id): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare('DELETE FROM linea_pedido WHERE pedido_id = ?');
        if (!$stmt) {
            $conn->close();
            return false;
        }
        $stmt->bind_param('i', $pedido_id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public function calcTotal(int $pedido_id): float
    {
        $total = 0.0;
        foreach ($this->getByPedido($pedido_id) as $linea) {
            $total += $linea->getSubtotal();
        }
        return $total;
    }
}

if (!class_exists('LineaPedidoRepository', false)) {
    class_alias('OrderLineRepository', 'LineaPedidoRepository');
}
