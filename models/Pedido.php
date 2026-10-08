<?php

class Pedido
{
    private int $id;
    private int $user_id;
    private DateTime $fecha;
    private string $estado;
    private float $precio_total;

    public function __construct(
        int $id,
        int $user_id,
        DateTime $fecha,
        string $estado = 'pendiente',
        float $precio_total = 0
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->fecha = $fecha;
        $this->estado = $estado;
        $this->precio_total = $precio_total;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getFecha(): DateTime
    {
        return $this->fecha;
    }

    public function setFecha(DateTime $fecha): void
    {
        $this->fecha = $fecha;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    public function getPrecioTotal(): float
    {
        return $this->precio_total;
    }

    public function setPrecioTotal(float $precio_total): void
    {
        $this->precio_total = $precio_total;
    }

    public static function create(int $user_id, float $precio_total = 0, string $estado = 'pendiente'): int|false
    {
        $conectar = db::connect();
        $stmt = $conectar->prepare('INSERT INTO pedido (user_id, estado, precio_total) VALUES (?, ?, ?)');
        if (!$stmt) {
            $conectar->close();
            return false;
        }
        $stmt->bind_param('isd', $user_id, $estado, $precio_total);
        if (!$stmt->execute()) {
            $stmt->close();
            $conectar->close();
            return false;
        }
        $id = $conectar->insert_id;
        $stmt->close();
        $conectar->close();
        return $id;
    }

    public static function getById(int $id): ?array
    {
        $conectar = db::connect();
        $stmt = $conectar->prepare('SELECT * FROM pedido WHERE id = ?');
        if (!$stmt) {
            $conectar->close();
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conectar->close();
        return $row ?: null;
    }
}