<?php

declare(strict_types=1);

namespace App\Model;

use App\Db;

abstract class ActiveRecordEntity
{
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __set($name, $value)
    {
        $camelCaseName = $this->underScoreToCamelCase($name);
        $this->$camelCaseName = $value;
    }

    private function underScoreToCamelCase(string $str): string
    {
        return lcfirst(str_replace('_', '', ucwords($str, '_')));
    }

    private function camelCaseToUnderscore(string $source): string
    {
        return strtolower(preg_replace('/([A-Z])/', '_$1', $source));
    }

    private function mapPropertiesToDbFormat(): array
    {

        $reflector = new \ReflectionObject($this);
        $properties = $reflector->getProperties();
        $mappedProperties = [];
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertyToDbFormat = $this->camelCaseToUnderscore($propertyName);
            $mappedProperties[$propertyToDbFormat] = $this->$propertyName;
        }

        return $mappedProperties;
    }

    public function save(): void
    {
        $mappedProperties = $this->mapPropertiesToDbFormat();
        if ($mappedProperties['id'] !== null) {
            $this->update($mappedProperties);
        } else {
            $this->insert($mappedProperties);
        }
    }

    public function update(array $mappedProperties): void
    {
        $db = Db::getInstance();
        $columnsToUpdate = [];
        $params = [];

        foreach ($mappedProperties as $column => $value) {
            $param = ':' . $column;
            $columnsToUpdate[] = "`$column` = $param";
            $params[$param] = $value;
        }

        $sql = 'UPDATE `' . static::getTableName() . '` SET ' . implode(', ', $columnsToUpdate) . ' WHERE `id` = :id';
        $params[':id'] = $this->id;

        $db->query($sql, $params, static::class);
    }


    public function insert(array $mappedProperties): void
    {
        $db = Db::getInstance();
        $filteredProperties = array_filter($mappedProperties);
        $columns = [];
        $params = [];
        $placeholders = [];

        foreach ($filteredProperties as $column => $value) {
            $columns[] = '`' . $column . '`';
            $paramName = ':' . $column;
            $params[$paramName] = $value;
            $placeholders[] = $paramName;
        }

        $sql = 'INSERT INTO `' . static::getTableName() . '` (' . implode(',', $columns) . ') VALUES (' . implode(',', $placeholders) . ')';

        $db->query($sql, $params);
        $this->id = $db->getLastInsertId();
    }

    public static function findAll(): ?array
    {
        $db = Db::getInstance();

        return $db->query('SELECT * FROM `' . static::getTableName() . '`', [], static::class);
    }

    public static function getById(int $id): ?static
    {
        $db = Db::getInstance();
        $entities = $db->query('SELECT * FROM `' . static::getTableName() . '` WHERE `id`=:id', [':id' => $id], static::class);

        return $entities ? $entities[0] : null;
    }

    public static function where(string $column, string $operator, string $value): ?static
    {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE ' . "$column $operator :$column";
        $entities = $db->query($sql, [":$column" => $value], static::class);

        return $entities ? $entities[0] : null;
    }

    public function destroy(): void
    {
        $db = Db::getInstance();
        $sql = 'DELETE FROM `' . static::getTableName() . '` WHERE id=:id';
        $db->query($sql, [':id' => $this->id], static::class);
        $this->id = null;
    }

    abstract protected static function getTableName(): string;
}
