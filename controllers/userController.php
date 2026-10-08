<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/UserRepository.php';

class UserController {
    private function ensureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit();
    }

    /** Soporta loginView (nombre/password) y formato antiguo (username/password). */
    public function doLogin(): void {
        $this->ensureSession();
        $login = $_POST['nombre'] ?? $_POST['username'] ?? $_POST['correo'] ?? '';
        $password = $_POST['password'] ?? $_POST['contraseña'] ?? '';
        $login = trim((string)$login);
        if ($login === '' || $password === '') {
            $_SESSION['flash_error'] = 'Indica usuario y contraseña.';
            $this->redirect('index.php?action=login');
        }
        try {
            $repo = new UserRepository();
            $user = $repo->getUserByUsername($login);
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Error de BD: ' . $e->getMessage();
            $this->redirect('index.php?action=login');
        }
        if ($user && password_verify((string)$password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            unset($_SESSION['flash_error']);
            $this->redirect('index.php');
        }
        $_SESSION['flash_error'] = 'Usuario o contraseña no válidos.';
        $this->redirect('index.php?action=login');
    }

    /** registerView ampliado: nombre, apellidos, correo, password. */
    public function doRegister(): void {
        $this->ensureSession();
        $nombre = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($nombre === '' || $apellidos === '' || $correo === '' || $password === '') {
            $_SESSION['flash_error'] = 'Nombre, apellidos, correo y contraseña son obligatorios.';
            $this->redirect('index.php?action=register');
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Correo no válido.';
            $this->redirect('index.php?action=register');
        }
        try {
            $repo = new UserRepository();
            if ($repo->getByCorreo($correo)) {
                $_SESSION['flash_error'] = 'Ese correo ya está registrado.';
                $this->redirect('index.php?action=register');
            }
            $user = new User(0, $nombre, $apellidos, $correo, (string)$password);
            $id = $repo->create($user);
            $_SESSION['user_id'] = $id;
            $_SESSION['user_nombre'] = $nombre;
            unset($_SESSION['flash_error']);
            $this->redirect('index.php');
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'No se pudo registrar: ' . $e->getMessage();
            $this->redirect('index.php?action=register');
        }
    }

    public function doLogout(): void {
        $this->ensureSession();
        session_destroy();
        $this->redirect('index.php');
    }
}
