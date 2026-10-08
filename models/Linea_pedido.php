<?php

class LineaPedido
{
    private int $id;
    private int $pedido_id;
    private int $producto_id;
    private int $cantidad;
    private float $precio_unitario;

    public function __construct(
        int $id = 0,
        int $pedido_id = 0,
        int $producto_id = 0,
        int $cantidad = 1,
        float $precio_unitario = 0
    ) {
        $this->id = $id;
        $this->pedido_id = $pedido_id;
        $this->producto_id = $producto_id;
        $this->cantidad = $cantidad;
        $this->precio_unitario = $precio_unitario;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPedidoId(): int
    {
        return $this->pedido_id;
    }

    public function setPedidoId(int $pedido_id): void
    {
        $this->pedido_id = $pedido_id;
    }

    public function getProductoId(): int
    {
        return $this->producto_id;
    }

    public function setProductoId(int $producto_id): void
    {
        $this->producto_id = $producto_id;
    }

    public function getCantidad(): int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): void
    {
        $this->cantidad = $cantidad;
    }

    public function getPrecioUnitario(): float
    {
        return $this->precio_unitario;
    }

    public function setPrecioUnitario(float $precio_unitario): void
    {
        $this->precio_unitario = $precio_unitario;
    }

    public function getSubtotal(): float
    {
        return $this->cantidad * $this->precio_unitario;
    }
}

if (!class_exists('Linea_pedido', false)) {
    class_alias('LineaPedido', 'Linea_pedido');
}
