<?php
namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Thin PDO wrapper (singleton).
 * All queries MUST go through prepared statements. Never concatenate input.
 */
class Database
{
    protected static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = Config::get('database');
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['driver'],
            $cfg['host'],
            $cfg['port'],
            $cfg['database'],
            $cfg['charset']
        );

        try {
            self::$pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
        } catch (PDOException $e) {
            // Never leak credentials/SQL to the user.
            Logger::error('DB connection failed: ' . $e->getMessage());
            throw new RuntimeException('No se pudo conectar a la base de datos.');
        }

        return self::$pdo;
    }

    /** Run a prepared statement and return the PDOStatement. */
    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch a single row (assoc) or null. */
    public static function selectOne(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** Fetch all rows. */
    public static function select(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** Fetch a single scalar value. */
    public static function scalar(string $sql, array $params = [])
    {
        return self::run($sql, $params)->fetchColumn();
    }

    /** Insert and return last insert id. */
    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int) self::connection()->lastInsertId();
    }

    /** Execute and return affected row count. */
    public static function execute(string $sql, array $params = []): int
    {
        return self::run($sql, $params)->rowCount();
    }

    /**
     * Transaction nesting depth. PDO has no native nested transactions, so a
     * service that opens its own transaction (e.g. VehicleStatusService) while
     * already inside a controller transaction would throw "There is already an
     * active transaction". We count depth and only touch PDO at the outermost
     * level, turning inner begin/commit into no-ops.
     */
    protected static int $txDepth = 0;

    public static function beginTransaction(): void
    {
        if (self::$txDepth === 0) {
            self::connection()->beginTransaction();
        }
        self::$txDepth++;
    }

    public static function commit(): void
    {
        if (self::$txDepth === 0) { return; }
        self::$txDepth--;
        if (self::$txDepth === 0 && self::connection()->inTransaction()) {
            self::connection()->commit();
        }
    }

    public static function rollBack(): void
    {
        // Any rollback unwinds the whole outer transaction — inner work cannot be
        // partially kept without savepoints, and atomic-all-or-nothing is what
        // every caller here expects.
        self::$txDepth = 0;
        if (self::connection()->inTransaction()) {
            self::connection()->rollBack();
        }
    }
}
