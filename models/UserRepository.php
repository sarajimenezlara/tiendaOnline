<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/User.php';

class UserRepository
{
    private static function mapRow(array $row): User
    {
        return new User(
            (int)$row['id'],
            $row['nombre'],
            $row['apellidos'],
            $row['correo'],
            $row['contraseña'],
            $row['telefono'] ?? null,
            $row['metodo_pago'] ?? null,
            $row['direccion'] ?? null
        );
    }

    /** @return User[] */
    public function getAll(): array
    {
        $conn = db::connect();
        $result = $conn->query('SELECT * FROM user ORDER BY id ASC');
        $users = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = self::mapRow($row);
            }
            $result->free();
        }
        $conn->close();
        return $users;
    }

    public function getById(int $id): ?User
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM user WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->close();
        return $row ? self::mapRow($row) : null;
    }

    public function getByCorreo(string $correo): ?User
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM user WHERE correo = ?');
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->close();
        return $row ? self::mapRow($row) : null;
    }

    /**
     * Compatibilidad con userController.php: busca por nombre o correo
     * y devuelve array con claves 'id' y 'password' (alias de 'contraseña').
     */
    public function getUserByUsername(string $username): ?array
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM user WHERE nombre = ? OR correo = ? LIMIT 1');
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->close();
        if (!$row) {
            return null;
        }
        // Aliases para el controlador antiguo
        $row['password'] = $row['contraseña'];
        $row['username'] = $row['nombre'];
        return $row;
    }

    /**
     * Crea un usuario. Si la contraseña no está hasheada, la hashea.
     * @return int id insertado
     */
    public function create(User $user): int
    {
        $conn = db::connect();
        $stmt = $conn->prepare(
            'INSERT INTO user (nombre, apellidos, correo, contraseña, telefono, metodo_pago, direccion) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $nombre = $user->getNombre();
        $apellidos = $user->getApellidos();
        $correo = $user->getCorreo();
        $pass = $user->getContraseña();
        if (password_get_info($pass)['algo'] === null) {
            $pass = password_hash($pass, PASSWORD_DEFAULT);
        }
        $telefono = $user->getTelefono();
        $metodo = $user->getMetodoPago();
        $direccion = $user->getDireccion();
        $stmt->bind_param('sssssss', $nombre, $apellidos, $correo, $pass, $telefono, $metodo, $direccion);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        return $id;
    }

    public function update(User $user): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare(
            'UPDATE user SET nombre = ?, apellidos = ?, correo = ?, telefono = ?, metodo_pago = ?, direccion = ? WHERE id = ?'
        );
        $nombre = $user->getNombre();
        $apellidos = $user->getApellidos();
        $correo = $user->getCorreo();
        $telefono = $user->getTelefono();
        $metodo = $user->getMetodoPago();
        $direccion = $user->getDireccion();
        $id = $user->getId();
        $stmt->bind_param('ssssssi', $nombre, $apellidos, $correo, $telefono, $metodo, $direccion, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public function delete(int $id): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare('DELETE FROM user WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }
}
