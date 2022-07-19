<?php

namespace eru123\NoEngine\Database;

use \PDOStatement;
use \PDO;

/**
 * Extended PDOStatement class.
 */
class XPDOStatement extends PDOStatement
{

    public $pdo;

    protected function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get the PDO Datatype equivalent of a PHP type
     */
    public static function get_pdo_data_type($type)
    {
        $type = strtolower($type);
        if (is_int($type)) {
            return PDO::PARAM_INT;
        } else if (is_bool($type)) {
            return PDO::PARAM_BOOL;
        } else if (is_null($type)) {
            return PDO::PARAM_NULL;
        }

        return PDO::PARAM_STR;
    }

    /**
     * Bind array of values to the statement.
     */
    public function bind(array $values)
    {
        foreach ($values as $key => $value) {
            $this->bindValue($key + 1, $value, self::get_pdo_data_type($value));
        }
        return $this;
    }

    /**
     * Execute the prepared statement and return the row count.
     */
    public function exec(){
        $this->execute();
        return $this->rowCount();
    }

    /**
     * Execute the prepared statement and return the first row.
     */
    public function get(...$args){
        $this->execute();
        return $this->fetch(...$args);
    }

    /**
     * Execute the prepared statement and return all rows.
     */
    public function getAll(...$args){
        $this->execute();
        return $this->fetchAll(...$args);
    }
}
