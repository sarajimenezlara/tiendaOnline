<?php

class db {
    public static function connect(){
        return new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], 
        $_ENV['DB_PASS'], $_ENV['DB_NAME']);
    }
}
?>