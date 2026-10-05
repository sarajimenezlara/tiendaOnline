<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Pedido.php';

class OrderRepository
{
    private static function mapRow(array $row): Pedido
    {
        return new Pedido(
            (int)$row['id'],
            (int)$row['user_id'],
            new DateTime($row['fecha']),
            $row['estado'],
            (float)$row['precio_total']
        );
    }

    public function getById(int $id): ?Pedido
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM pedido WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->close();
        return $row ? self::mapRow($row) : null;
    }

    /** @return Pedido[] */
    public function getByUser(int $user_id): array
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM pedido WHERE user_id = ? ORDER BY fecha DESC');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedidos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pedidos[] = self::mapRow($row);
            }
        }
        $stmt->close();
        $conn->close();
        return $pedidos;
    }

    /** @return Pedido[] */
    public function getAll(): array
    {
        $conn = db::connect();
        $result = $conn->query('SELECT * FROM pedido ORDER BY fecha DESC');
        $pedidos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pedidos[] = self::mapRow($row);
            }
            $result->free();
        }
        $conn->close();
        return $pedidos;
    }

    public function create(int $user_id, float $precio_total = 0, string $estado = 'pendiente'): int
    {
        $conn = db::connect();
        $stmt = $conn->prepare('INSERT INTO pedido (user_id, estado, precio_total) VALUES (?, ?, ?)');
        $stmt->bind_param('isd', $user_id, $estado, $precio_total);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        return $id;
    }

    public function updateEstado(int $id, string $estado): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare('UPDATE pedido SET estado = ? WHERE id = ?');
        $stmt->bind_param('si', $estado, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public function updateTotal(int $id, float $precio_total): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare('UPDATE pedido SET precio_total = ? WHERE id = ?');
        $stmt->bind_param('di', $precio_total, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public function delete(int $id): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare('DELETE FROM pedido WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }
}

// Aliases para compatibilidad español / inglés
if (!class_exists('PedidoRepository', false)) {
    class_alias('OrderRepository', 'PedidoRepository');
}
