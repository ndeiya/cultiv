<?php
/**
 * Database Configuration
 * Returns a singleton PDO instance for MySQL connection.
 */

class Database
{
    private static $instance = null;
    const DB_CHARSET = 'utf8mb4';

    /**
     * Get the singleton PDO instance.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $name = getenv('DB_NAME') ?: 'cultiv';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $host,
                $port,
                $name,
                self::DB_CHARSET
            );

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                ]);
            } catch (PDOException $e) {
                if (defined('APP_DEBUG') && APP_DEBUG) {
                    die('Database connection failed: ' . $e->getMessage());
                }
                die('Database connection failed. Please check your configuration.');
            }
        }

        return self::$instance;
    }

    // Prevent cloning and unserialization
    private function __construct() {}
    private function __clone() {}
    public function __wakeup() { throw new \Exception('Cannot unserialize singleton'); }
}
