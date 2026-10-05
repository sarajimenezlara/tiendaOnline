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
}