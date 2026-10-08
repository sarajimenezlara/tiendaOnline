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
        $result = $conn->query('SELECT * FROM `user` ORDER BY id ASC');
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
        $stmt = $conn->prepare('SELECT * FROM `user` WHERE id = ?');
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

    public function getByCorreo(string $correo): ?User
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM `user` WHERE correo = ?');
        if (!$stmt) {
            $conn->close();
            return null;
        }
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->close();
        return $row ? self::mapRow($row) : null;
    }

    public function getUserByUsername(string $username): ?array
    {
        $conn = db::connect();
        $stmt = $conn->prepare('SELECT * FROM `user` WHERE nombre = ? OR correo = ? LIMIT 1');
        if (!$stmt) {
            $conn->close();
            return null;
        }
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->close();
        if (!$row) {
            return null;
        }
        $row['password'] = $row['contraseña'];
        $row['username'] = $row['nombre'];
        return $row;
    }

    public function getUserObjectByUsername(string $username): ?User
    {
        $row = $this->getUserByUsername($username);
        if (!$row) {
            return null;
        }
        return self::mapRow($row);
    }

    public function create(User $user): int
    {
        $conn = db::connect();
        $stmt = $conn->prepare(
            'INSERT INTO `user` (nombre, apellidos, correo, `contraseña`, telefono, metodo_pago, direccion) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            $conn->close();
            throw new RuntimeException('Prepare failed: ' . $conn->error);
        }
        $nombre = $user->getNombre();
        $apellidos = $user->getApellidos();
        $correo = $user->getCorreo();
        $pass = $user->getContraseña();
        if ($pass === '' ) {
            $stmt->close();
            $conn->close();
            throw new InvalidArgumentException('La contraseña no puede estar vacía');
        }
        if (password_get_info($pass)['algo'] === null) {
            $pass = password_hash($pass, PASSWORD_DEFAULT);
        }
        $telefono = $user->getTelefono();
        $metodo = $user->getMetodoPago();
        $direccion = $user->getDireccion();
        $stmt->bind_param('sssssss', $nombre, $apellidos, $correo, $pass, $telefono, $metodo, $direccion);
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

    public function update(User $user): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare(
            'UPDATE `user` SET nombre = ?, apellidos = ?, correo = ?, telefono = ?, metodo_pago = ?, direccion = ? WHERE id = ?'
        );
        if (!$stmt) {
            $conn->close();
            return false;
        }
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

    public function updatePassword(int $id, string $plainPassword): bool
    {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $conn = db::connect();
        $stmt = $conn->prepare('UPDATE `user` SET `contraseña` = ? WHERE id = ?');
        if (!$stmt) {
            $conn->close();
            return false;
        }
        $stmt->bind_param('si', $hash, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public function delete(int $id): bool
    {
        $conn = db::connect();
        $stmt = $conn->prepare('DELETE FROM `user` WHERE id = ?');
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
