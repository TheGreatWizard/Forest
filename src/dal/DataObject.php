<?php

namespace DAL;

/**
 * Defines object which contains data, and can be stored as single table row
 * Rules:
 * 1. dont initiate fileds in the class scope, only in the constractor
 */
abstract class DataObject
{

    /**
     * @mysql_type donotsave 
     */
    private static $dataClassNames = array();

    /**
     * @mysql_type donotsave 
     */
    private static $storableClassNames = array();

    /**
     * @mysql_type donotsave 
     */
    private static $tableNames = array();

    private static function addClass($tableName, $dataClass, $storableClass)
    {
        self::$tableNames[] = $tableName;
        self::$dataClassNames[] = $dataClass;
        self::$storableClassNames[] = $storableClass;
    }

    public static function isTableExists($tableName)
    {
        return in_array($tableName, self::$tableNames);
    }

    public static function isDataClassExists($dataClass)
    {

        return in_array($dataClass, self::$dataClassNames);
    }

    public static function getStorableFromClassName($className)
    {
        $index = array_search(ltrim($className, "\\"), self::$dataClassNames);
        if ($index === false) {
            throw new \Exception("Cannot find class name: $className, construct a dummy object before loading or storing");
        }

        return self::$storableClassNames[$index];
    }

    public static function getStorableFromTableName($tableName)
    {
        $index = array_search($tableName, self::$tableNames);
        if ($index === false) {
            throw new \Exception("Cannot find table name: $tableName, construct a dummy object before loading or storing");
        }
        return self::$storableClassNames[$index];
    }

    public function __construct()
    {
        $uid = uniqid('', true); //\Login\Hasher::uniqueId(64);
        $this->id = $uid;

        $className = get_class($this);
        if (!self::isDataClassExists($className)) {
            $rc = new \ReflectionClass($className);
            $tableName = $rc->getShortName();
            $storableClassName = $this->getStorableClassName();
            self::addClass($tableName, $className, $storableClassName);
        }
    }

    /**
     * @mysql_type donotsave 
     */
    public $dataType = 0;

    /**
     * @mysql_type donotsave 
     */
    public $hasMetaData = true;

    /**
     * unique id of the object
     * @mysql_type VARCHAR(64) UNIQUE
     * @type string
     */
    private $id = "";

    /**
     * if object has no id new id is generated
     * @return string object unique id
     */
    public function getId()
    {
        if (($this->id === "") || ($this->id === null)) {
            throw new \Exception("Data object has no id");
        }
        return $this->id;
    }

    /**
     * Set unique id of the dataobject, should be used only once.
     * @param type $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return CombinedId - DataObject class name and its id
     */
    public function getCombinedId()
    {
        $className = get_class($this);
        $index = array_search($className, self::$dataClassNames);
        if ($index===false) {
            throw new \Exception("the class $className not found, construnct dummy class.");
        }
        $tableName = self::$tableNames[$index];
        return [$tableName, $this->id];
    }

    public function getStorableClassName()
    {
        return get_class($this);
    }

    /**
     *  This function should be overriden if current object is not Storable and has different StorableObject.
     * @return \DAL\DataObject - this object
     */
    public function toStorable()
    {
        $storableObjectClassName = $this->getStorableClassName();
        if (get_class($this) == $storableObjectClassName) {
            return $this;
        }

        $storableObject = new $storableObjectClassName();
        $storableObject->id = $this->id;
        return $storableObject;
    }

    /**
     *  This function should be overriden if current object is Storable and has different DataObject.
     * @return \DAL\DataObject - this object
     */
    public function toDataObject()
    {
        return $this;
    }
    /* @todo : this functions will be raplaced by iterator function
     */
}
