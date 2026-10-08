<?php

class db {
    public static function connect(): mysqli {
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'root';
        // Acepta DB_PASS y DB_PASSWORD (.env antiguo)
        $pass = getenv('DB_PASS');
        if ($pass === false) {
            $pass = getenv('DB_PASSWORD');
        }
        if ($pass === false) {
            $pass = '';
        }
        $name = getenv('DB_NAME') ?: 'tiendaOnline';
        $port = (int)(getenv('DB_PORT') ?: 3306);

        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = new mysqli($host, $user, $pass, $name, $port);
        if ($conn->connect_error) {
            throw new RuntimeException('Error de conexión DB: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
        return $conn;
    }
}
