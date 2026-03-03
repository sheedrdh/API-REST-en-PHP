<?php
declare(strict_types=1);

class Database {
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            $host = '127.0.0.1';
            $db   = 'shop';
            $user = 'root';
            $pass = '';               // <‑ mot de passe root de XAMPP
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $opt = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            // création de la connexion – sécurité grâce à PDO
            self::$pdo = new PDO($dsn, $user, $pass, $opt);
        }
        return self::$pdo;
    }
}
