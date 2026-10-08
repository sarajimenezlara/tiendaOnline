<?php

class Pedido
{
    private int $id;
    private int $user_id;
    private DateTime $fecha;
    private string $estado;
    private float $precio_total;

    public function __construct(
        int $id = 0,
        int $user_id = 0,
        ?DateTime $fecha = null,
        string $estado = 'pendiente',
        float $precio_total = 0
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->fecha = $fecha ?? new DateTime();
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

    private static function repo(): OrderRepository
    {
        require_once __DIR__ . '/OrderRepository.php';
        return new OrderRepository();
    }

    public static function create($user_id, float $precio_total = 0, string $estado = 'pendiente'): int
    {
        return self::repo()->create((int)$user_id, $precio_total, $estado);
    }

    public static function getById($id): ?Pedido
    {
        return self::repo()->getById((int)$id);
    }

    /** @return Pedido[] */
    public static function getByUser($user_id): array
    {
        return self::repo()->getByUser((int)$user_id);
    }

    /** @return Pedido[] */
    public static function getAll(): array
    {
        return self::repo()->getAll();
    }
}
