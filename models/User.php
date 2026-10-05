<?php

class User
{
    private int $id;
    private string $nombre;
    private string $apellidos;
    private string $correo;
    private string $contraseña;
    private ?string $telefono;
    private ?string $metodo_pago;
    private ?string $direccion;

    public function __construct(
        int $id,
        string $nombre,
        string $apellidos,
        string $correo,
        string $contraseña,
        ?string $telefono = null,
        ?string $metodo_pago = null,
        ?string $direccion = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->correo = $correo;
        $this->contraseña = $contraseña;
        $this->telefono = $telefono;
        $this->metodo_pago = $metodo_pago;
        $this->direccion = $direccion;
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

    public function getApellidos(): string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): void
    {
        $this->apellidos = $apellidos;
    }

    public function getCorreo(): string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): void
    {
        $this->correo = $correo;
    }

    public function getContraseña(): string
    {
        return $this->contraseña;
    }

    public function setContraseña(string $contraseña): void
    {
        $this->contraseña = $contraseña;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): void
    {
        $this->telefono = $telefono;
    }

    public function getMetodoPago(): ?string
    {
        return $this->metodo_pago;
    }

    public function setMetodoPago(?string $metodo_pago): void
    {
        $this->metodo_pago = $metodo_pago;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): void
    {
        $this->direccion = $direccion;
    }
}