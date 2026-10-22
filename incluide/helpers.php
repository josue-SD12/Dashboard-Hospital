<?php
/**
 * Conexión a MySQL con PDO (patrón singleton).
 * Uso:  $pdo = Conexion::get();
 */
class Conexion
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $c   = require __DIR__ . '/config.php';
            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['dbname']};charset={$c['charset']}";
            try {
                self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // No exponer credenciales en el mensaje
                error_log('Error de conexión: ' . $e->getMessage());
                http_response_code(500);
                exit(json_encode(['error' => 'No se pudo conectar a la base de datos']));
            }
        }
        return self::$pdo;
    }
}
