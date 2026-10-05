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

    public static function login($nombre, $contraseña) {
        $conectar = db::connect();
        $stmt = $conectar->prepare("SELECT * FROM user WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($contraseña, $user['contraseña'])) {
            return $user;
        }
        return false;
    }

    public static function register($nombre, $apellidos, $contraseña, $telefono = null, $metodo_pago = null, $direccion = null) {
        $conectar = db::connect();
        
        // Verificar si el nombre ya existe
        $stmt = $conectar->prepare("SELECT id FROM user WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            return false; // El usuario ya existe
        }
        
        // Cifrar la contraseña
        $hash = password_hash($contraseña, PASSWORD_DEFAULT);
        
        // Insertar el nuevo usuario
        $stmt = $conectar->prepare("INSERT INTO user (nombre, apellidos, correo, contraseña, telefono, metodo_pago, direccion) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $correo = ""; // Por ahora vacío
        $stmt->bind_param("sssssss", $nombre, $apellidos, $correo, $hash, $telefono, $metodo_pago, $direccion);
        
        if ($stmt->execute()) {
            return $conectar->insert_id; // Devuelve el id del nuevo usuario
        }
        return false;
    }
}