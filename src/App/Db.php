<?php

declare(strict_types=1);

namespace App;

use PDO;

class Db
{
    private PDO $pdo;
    private static DB $instance;

    private function __construct()
    {
        $dbOptions = require dirname(__DIR__) . '/config/options.php';
        $this->pdo = new PDO(
            'mysql:host=' . $dbOptions['host'] . ';dbname=' . $dbOptions['dbname'],
            $dbOptions['user'],
            $dbOptions['password']
        );
        $this->pdo->exec('SET NAMES UTF8');
    }

    public static function getInstance(): Db
    {
        return static::$instance ?? static::$instance = new static();
    }

    public function query(string $sql, $params = [], string $className = 'stdClass'): false|array|null
    {
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($params);

        if ($result === false) {
            return null;
        }
        return $stmt->fetchAll(PDO::FETCH_CLASS, $className);
    }

    public function getLastInsertId(): int
    {
        return (int)$this->pdo->lastInsertId();
    }
}
