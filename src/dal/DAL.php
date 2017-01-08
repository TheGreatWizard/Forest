<?php

namespace DAL;

/**
 * Responsibility:
 * 1. Connection to mysql database
 * 2. SQL query execution
 * 3. Table manipulation functions
 * Singleton
 *
 * Uses the following constants:
 * DB_HOST,  DB_NAME, DB_USER, DB_PASSWORD
 *
 * @author Grigory Ilizirov
 * @date 2016-May-27 3:27:11 PM
 */
// @todo: exception missing in some functions
class DAL
{
    private $pdo;
    private static $instance = null;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new DAL();
        }
        return self::$instance;
    }

    public function getTables()
    {
        $theQuery = $this->query("show tables");
        $tableList = $theQuery->fetchAll(\PDO::FETCH_NUM);
        foreach ($tableList as $table) {
            $name = $table[0];
            $newTable = new Table($name);
            $newTable->UpdateFieldsArray();
            $tables [] = $newTable;
        }
        return $tables;
    }

    public function getTableNames()
    {
        $tableNames = array();
        $theQuery = $this->query("show tables");
        $tables = $theQuery->fetchAll(\PDO::FETCH_NUM);
        foreach ($tables as $table) {
            $tableNames [] = $table[0];
        }
        return $tableNames;
    }

    // @todo: change constants to constructor parameters
    // @todo: use proper Exception mechanism
    private function __construct()
    {
        try {
            $this->pdo = new \PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
        } catch (PDOException $e) {
            throw new \Exception("DAL::__construct()", $e);
        }
    }

    /**
     * Preparing, executing, and returning the PDO query
     *
     * @param string $sql - query string
     * @param array $params - query parameters
     * @return PDOStatement
     */
    public function query($sql, $params = array())
    {
        if (!($theQuery = $this->pdo->prepare($sql))) {
            throw new \Exception('Faild to prepare query :' . "Query[" . $sql . "]");
        }
        if (count($params)) {
            foreach ($params as $key => $param) {
                $theQuery->bindValue($key + 1, $param);
            }
        }

        if ($theQuery->execute()) {
            return $theQuery;
        } else {
            throw new \Exception('Query execution error:' .
            json_encode($theQuery->errorInfo()) . "\n Query[" . $sql . "]");
        }

        return;
    }

    /**
     * Creates new table in the database.
     * @param Table $table
     */
    public function createTable($table)
    {
        $sql = "CREATE TABLE " . $table->name . " (";
        $first = true;
        foreach ($table->fields_arr as $field) {
            if ($first) {
                $first = false;
            } else {
                $sql .= ",";
            }
            $sql .= $field->getSQL();
        }
        $sql .= ")";
        $this->query($sql);
    }

    /**
     * Drops table from the database.
     * @param string $tableName
     */
    public function dropTable($tableName)
    {
        $sql = "DROP TABLE " . $tableName;
        $this->query($sql);
    }

    /**
     * Check if table exists, if so drops it from the database.
     * @param string $tableName
     */
    public function dropTableIfExists($tableName)
    {
        if ($this->isTableExists($tableName)) {
            $this->dropTable($tableName);
        }
    }

    /**
     * Check if table exists (by name), if so drops it from the database.
     * Then create it again from given object.
     * @param Table $table
     */
    public function recreateTable($table)
    {
        if ($this->isTableExists($table->name)) {
            $this->DropTable($table->name);
        }
        $this->CreateTable($table);
    }

    /**
     * Creates new table object from table in database
     * @param string $tableName
     * @return Table
     */
    public function getTableByName($tableName)
    {
        if ($this->isTableExists($tableName)) {
            $newTable = new Table($tableName);
            $newTable->UpdateFieldsArray();
            return $newTable;
        }
        throw new \Exception("Table not found: " . $tableName);
    }

    /**
     * Check if table exists in the database
     * @param string $tableName
     * @return boolean
     */
    public function isTableExists($tableName)
    {
        $theQuery = $this->query("show tables like ?", array(
            $tableName
        ));
        $table = $theQuery->fetchAll(\PDO::FETCH_NUM);
        if (count($table) > 0) {
            return (strtolower($table [0] [0]) === strtolower($tableName));
        } else {
            return false;
        }
    }

    /**
     * Creates Field array from table in database. Prevents the need for templorary Table object.
     * WARNING: this function is not secured properly !
     * @param string $tableName
     * @return \DAL\Field[]
     */
    public function getTableFields($tableName)
    {
        $pdoStatement = $this->query("DESC {$tableName}");
        $fields = array();
        while ($tableField = $pdoStatement->fetch(\PDO::FETCH_ASSOC)) {
            $field = new Field();
            $field->setFromMysqlDesc($tableField);
            $fields [] = $field;
        }

        return $fields;
    }
}

// class DAL
