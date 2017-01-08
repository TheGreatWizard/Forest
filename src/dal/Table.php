<?php

namespace DAL;

class Table
{

    public $name = "";
    public $fields_arr = array();
    public $type_arr = array();
    protected $DAL;

    /**
     * Construct new table object from table nama and fields array
     * To create table in the database use DAL\DAL::createTable()
     * @param string $name - the table name
     * @param array $fields_arr - array of field objects
     */
    public function __construct($name = '', $fields_arr = array())
    {
        if ($name !== '') {
            $this->setName($name);
            if ($fields_arr !== array()) {
                $this->fields_arr = $fields_arr;
            }
            $this->DAL = DAL::getInstance();
        }
    }
// function __construct

    /**
     * Converts given value to value witch can be stored in table.
     * Array converted to json string, and objects converted according to thier class
     * This function is used when object converted to row in this::addRowFromObject()
     * The oposite function is self::convertType(), this functions must be consistent.
     * @param mixed $val
     * @throws \Exception
     */
    private static function makeValueStorable($val)
    {
        if (is_array($val)) {
            return json_encode($val);
        }
        if (is_object($val)) {
            $className = get_class($val);
            if ($className === "DateTimeImmutable") {
                return $val->format('Y-m-d H:i:s');
            } else {
                throw new \Exception("object storage is not implemented:" . get_class($val));
            }
        }
        return $val;
    }

    /**
     * Converts the value to specified type.
     * if $type=="array" the value converted to json string
     * The oposite function is self::makeValueStorable(), this functions must be consistent.
     * @param string $type
     * @param mixed $value
     */
    private static function convertType($type, $value)
    {
        if (strpos($type, 'int') !== false) {
            return (int) $value;
        } elseif (strpos($type, 'array') !== false) {
            return json_decode($value);
        } elseif (strpos($type, 'DateTimeImmutable') !== false) {
            return new \DateTimeImmutable($value);
        } elseif (strpos($type, 'float') !== false) {
            return (float) $value;
        } else {
            return (string) $value;
        }
    }

    /**
     * @param string $className
     * @returns array of object \ReflectionProperty.
     */
    private static function getClassProperties($className, $filter = null)
    {
        $ref = new \ReflectionClass($className);
        if ($filter == null) {
            $props = $ref->getProperties();
        } else {
            $props = $ref->getProperties($filter);
        }
        if ($parentClass = $ref->getParentClass()) {
            $parent_private_props_arr = self::getClassProperties($parentClass->getName(), \ReflectionProperty::IS_PRIVATE); // RECURSION
            if (count($parent_private_props_arr) > 0) {
                $props = array_merge($parent_private_props_arr, $props);
            }
        }

        return $props;
    }

    /**
     * Extracts the type of a property from DocComment, using type tag
     * @param \ReflectionProperty $prop
     */
    private static function getTypeFromProperty($prop)
    {
        $comment_string = $prop->getDocComment();
        //define the regular expression pattern to use for string matching
        $pattern = "#(@type+\s*[a-zA-Z0-9, ()_].*)#";

        //perform the regular expression on the string provided
        preg_match_all($pattern, $comment_string, $matches, PREG_PATTERN_ORDER);

        if (count($matches[0]) > 0) {
            $split = explode(' ', $matches [0] [0]);
            if (count($split) > 1) {
                $propType = $split[1];
            } else {
                $propType = "string";
            }
        } else {
            $propType = "string";
        }

        return $propType;
    }

    /**
     * Extracts the mysql field type of a property from DocComment, using mysql_type tag
     * @param \ReflectionProperty $prop
     */
    private static function getMySqlTypeFromProperty($prop)
    {
        $comment_string = $prop->getDocComment();
        //define the regular expression pattern to use for string matching
        $pattern = "#(@mysql_type+\s*[a-zA-Z0-9, ()_].*)#";

        //perform the regular expression on the string provided
        preg_match_all($pattern, $comment_string, $matches, PREG_PATTERN_ORDER);
        if (count($matches[0]) > 0) {
            $propType = trim(str_replace("@mysql_type ", "", $matches [0] [0]));
        } else {
            $propType = "TEXT";
        }

        return $propType;
    }

    /**
     * A new table is constructed from the given object.
     * Object fields names become table fields
     * The table is created in the database, therefore check if the name avaible before calling this function.
     * @param string $name - new table name
     * @param object $object
     */
    public static function constructTableFromObject($name, $object)
    {
        $objectArray = (array) $object;
        $fieldArray = array();

        $objectProperties = self::getClassProperties($object);
        //var_dump($objectProperties);

        foreach ($objectProperties as $prop) {
            $propName = $prop->getName();
            $propType = self::getMySqlTypeFromProperty($prop);
            if (strtolower($propType) != "donotsave") {
                $field = new \DAL\Field();
                $field->setName($propName);
                $field->setType($propType);

                $fieldArray[] = $field;
            }
        }

        $instance = new Table($name, $fieldArray);
        $dal = DAL::getInstance();
        $dal->createTable($instance);
        return $instance;
    }

    /**
     * Converts row to object
     * @param array $row - item of output array of selectRows()
     * @param string $className - the full class name of the object
     */
    public function rowToObject($row, $className)
    {
        $recordClass = $className;
        $recordProperties = self::getClassProperties($recordClass);
        #$record = new $recordClass();
        $r = new \ReflectionClass($recordClass);
        $record = $r->newInstanceWithoutConstructor();


        foreach ($recordProperties as $rp) {
            $propName = $rp->getName();

            if (array_key_exists($propName, $row)) {
                $propType = self::getTypeFromProperty($rp);
                //echo "\n" . $propName . "(" . $propType . ")";
                //var_dump($row[$propName]);
                $propValue = self::convertType($propType, $row[$propName]);
                $rp->setAccessible(true);
                $rp->setValue($record, $propValue);
            }
        }
        return $record;
    }

    /**
     * Set table name
     * @param unknown $name
     * @throws Exception
     */
    public function setName($name)
    {
        if (!preg_match('/[^A-Za-z0-9_]/', $name)) { // OPOSITE?
            // string contains only english letters & digits
            $this->name = $name;
        } else {
            throw new Exception('Bad table name. Table name can contain only english letters & digits');
        }
    }

// function setName($name)

    public function updateFieldsArray()
    {
        if ($this->DAL->isTableExists($this->name)) {
            $q = $this->DAL->query("DESC {$this->name}");

            $this->fields_arr = array();
            while ($table_field = $q->fetch(\PDO::FETCH_ASSOC)) {
                $field = new Field();
                $field->setFromMysqlDesc($table_field);
                $this->fields_arr[] = $field;
            }
        } else {
            throw new Exception('Table ' . $this->name . ' not exists');
        }
    }

    public function objectToRow($object)
    {
        if (count($this->fields_arr) == 0) {
            $this->updateFieldsArray();
        }

        $fieldsNames = array_map(function($x) {
            return $x->getName();
        }, $this->fields_arr);
        //var_dump($fieldsNames);

        $arr = (array) $object;
        $storableArray = array();
        foreach ($arr as $key => $value) {
            $keyArray = explode(chr(0), $key);
            $propName = end($keyArray);
            //echo "\n".$propName;
            if (!in_array($propName, $fieldsNames)) {

                continue;
            }
            $storableArray [$propName] = self::makeValueStorable($value);
        }
        return $storableArray;
    }

    /**
     * @param unknown $object
     */
    public function addRowFromObject($object)
    {
        $arr = $this->objectToRow($object);

        $this->addRow($arr);
    }

    public function addRow($arr)
    {
        #var_dump($arr);
        $params = []; //array_values($arr);
        if (count($this->fields_arr) == 0) {
            $this->updateFieldsArray();
        }
        $fields_names = "";
        foreach ($this->fields_arr as $currentField) {
            $fieldName = $currentField->getName();
            if (!array_key_exists($fieldName, $arr)) {
                continue;
            }

            if ($fields_names == "") {
                $fields_names = $fieldName; //$cur_field->getName();
                $values_marks = "?";
            } else {
                $fields_names = $fields_names . "," . $fieldName;
                $values_marks = $values_marks . ",?";
            }
            $params[] = $arr[$fieldName];
        }

        $sql = "INSERT INTO " . $this->name . " (" . $fields_names . ") VALUES (" . $values_marks . ")";
        //echo $sql;
        $this->DAL->query($sql, $params);
    }

// function  addRow($arr)

    private function cast($val, $type)
    {
        //echo "\n ".$val." ".$type;
        switch ($type) {
            case "integer":
                return intval($val);
                break;
            case "double":
                return floatval($val);
                break;
            case "string":
                return strval($val);
                break;
            case "json":
                return json_decode($val);
                break;
        }
    }

//  function Cast($val,$type)

    public function selectRows($where = "", $params = array())
    {
        if (!is_array($params)) {
            $params = array($params);
        }
        //echo "|".$where."|";
        //var_dump($params);
        $sql = "SELECT * FROM " . $this->name;
        if ($where != "") {
            $sql = $sql . " WHERE " . $where;
        }
        //echo "\n".$sql;
        $the_query = $this->DAL->query($sql, $params);
        $res = $the_query->fetchAll(\PDO::FETCH_NUM);
        //echo "\nThe first result: \n";
        //var_dump($res);
        $the_query->closeCursor();
        if (count($res) > 0) {
            $on_type = (count($this->type_arr) == count($res[0]));
        } else {
            $on_type = false;
        }
        $res_type = array();
        foreach ($res as $row) {
            $new_row = array();
            for ($i = 0; $i < count($row); $i++) {
                if ($on_type) {
                    $new_row[$this->fields_arr[$i]->getName()] = $this->Cast($row[$i], $this->type_arr[$i]);
                } else {
                    $new_row[$this->fields_arr[$i]->getName()] = $row[$i];
                }
            }
            $res_type[] = $new_row;
        }
        return $res_type;
    }

    /**
     * @param unknown $object
     */
    public function updateRowFromObject($object)
    {
        $arr = $this->objectToRow($object);
        if (!array_key_exists('id',$arr)){
            throw new \Exception("object must have id field in order to update");
        }
        
        $fields_names = "";
        foreach ($this->fields_arr as $currentField) {
            $fieldName = $currentField->getName();
            if ($fieldName=='id'){
                continue;
            }
            if (!array_key_exists($fieldName, $arr)) {
                continue;
            }

            if ($fields_names == "") {
                $fields_names = $fieldName."=?";
            } else {
                $fields_names = $fields_names . "," . $fieldName."=?";
            }
            $params[] = $arr[$fieldName];
        }
        
        $params[] = $arr['id'];

        $this->updateRow(" " . $fields_names . " ", "id = ?", $params);
    }

    public function updateRow($set = "", $where = "", $params = array())
    {
        if ($set == "") {
            throw new Exception("updateRow() faild, 'set' string is empty");
        }

        if ($where == "") {
            throw new Exception("updateRow() faild, 'where' string is empty");
        }

        $sql = "UPDATE " . $this->name;
        $sql = $sql . " SET " . $set;
        $sql = $sql . " WHERE " . $where;

        $the_query = $this->DAL->query($sql, $params);
    }

// UpdateRow($set ="", $where="", $params = array())

    public function deleteRows($where = "", $params = array())
    {
        // returns the number of rows deleted
        $sql = "DELETE FROM " . $this->name;
        if ($where != "") {
            $sql = $sql . " WHERE " . $where;
        }
        $the_query = $this->DAL->query($sql, $params);
        return $the_query->rowCount();
    }
}
