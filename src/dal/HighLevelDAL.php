<?php

namespace DAL;

class HighLevelDAL
{

    protected $dal;

    public function __construct()
    {
        $this->dal = \DAL\DAL::getInstance();
    }

    private function getOrCreateClassTable($obj)
    {
        $className = get_class($obj);

        // extract table name = short class name
        $tableName = join('', array_slice(explode('\\', $className), -1));

        // looking for the table in the database
        $tableExists = $this->dal->isTableExists($tableName);

        if ($tableExists) {
            //$this->dal->syncTableArr();
            return $this->dal->getTableByName($tableName);
        } else {
            $cls = new $className();
            $storableClassName = $cls->getStorableClassName();
            return \DAL\Table::constructTableFromObject($tableName, $storableClassName);
        }
    }

    /**
     * @param \DAL\DataObject $obj - object to store in the database
     * @param int $depth - the storage depth
     */
    public function saveDataObject($obj, $depth)
    {
        if (!is_a($obj, "\DAL\DataObject")) {
            var_dump($obj);
            throw new \Exception("Only \DAL\DataObject can be saved");
        }
        $id = $obj->getId();
        #  echo "\n".$id;      
        $table = $this->getOrCreateClassTable($obj);
        $storableObj = $obj->toStorable();
        if (!is_object($storableObj)) {
            throw new \Exception("No storable object returned by toStorable() function");
        }
        $table->addRowFromObject($storableObj);

        if (($obj->dataType > 0) && ($depth > 0)) {
            if ($obj->dataType == 1) {
                $this->saveDataObject($obj->data, $depth - 1);
            } elseif ($obj->dataType == 2) {
                foreach ($obj as $subObj) {
                    $this->saveDataObject($subObj, $depth - 1);
                }
            }
        }
    }

    public function deleteDataObject($obj, $depth)
    {
        if (!is_a($obj, "\DAL\DataObject")) {
            throw new \Exception("Only \DAL\DataObject can be deleted");
        }
        if ($this->isDataObjectExists($obj->getCombinedId())) {
            $id = $obj->getId();
            $table = $this->getOrCreateClassTable($obj);
            $table->deleteRows("id = ?", [$id]);
        }
        
        if (($obj->dataType > 0) && ($depth > 0)) {
            if ($obj->dataType == 1) {
                $this->deleteDataObject($obj->data, $depth - 1);
            } elseif ($obj->dataType == 2) {
                foreach ($obj as $subObj) {
                    $this->deleteDataObject($subObj, $depth - 1);
                }
            }
        }
    }

    /**
     * @param \DAL\DataObject $obj - object to update in the database
     * @param int $depth - the storage depth
     */
    public function updateDataObject($obj, $depth)
    {
        if (!is_a($obj, "\DAL\DataObject")) {
            throw new \Exception("Only \DAL\DataObject can be updated or saved");
        }
        $storableObj = $obj->toStorable();
        if (!is_object($storableObj)) {
            throw new \Exception("No storable object returned by toStorable() function");
        }


        $id = $obj->getId();
        $table = $this->getOrCreateClassTable($obj);
        $rows = $table->selectRows("id = ?", [$id]);
        if (count($rows) > 0) {
            $table->updateRowFromObject($storableObj);
        } else {
            $table->addRowFromObject($storableObj);
        }
        if (($obj->dataType > 0) && ($depth > 0)) {
            if ($obj->dataType == 1) {
                $this->updateDataObject($obj->data, $depth - 1);
            } elseif ($obj->dataType == 2) {
                foreach ($obj as $subObj) {
                    $this->updateDataObject($subObj, $depth - 1);
                }
            }
        }
    }

    /**
     *
     * @param array $combinedId - combinedId of the object to load
     * @param int $depth - depth of the loading
     * @return \DAL\DataObject
     * @throws \Exception
     */
    public function loadDataObject($combinedId, $depth)
    {
        list($tableName, $id) = $combinedId;
        # echo "\nL:".$id;      
        $table = $this->dal->getTableByName($tableName);
        $rows = $table->selectRows("id = ?", array($id));
        if (count($rows) < 1) {

            throw new \Exception("DataObject not found " . json_encode($combinedId));
        }

        $storableObject = $table->rowToObject($rows[0], DataObject::getStorableFromTableName($tableName));
        $dataObject = $storableObject->toDataObject();
        if (!is_object($dataObject)) {
            throw new \Exception("No data object returned by toDataObject() function");
        }
        if (($dataObject->dataType > 0) && ($depth > 0)) {
            if ($dataObject->dataType == 1) {
                $dataObject->data = $this->loadDataObject($dataObject->getDataCombinedId(), $depth - 1);
            } elseif ($dataObject->dataType == 2) {
                foreach ($dataObject as $key => $subObj) {
                    #  var_dump($key);
                    $newSubObj = $this->loadDataObject($key, $depth - 1);
                    $dataObject->setCurrent($newSubObj);
                }
            }
        }

        return $dataObject;
    }

    /**
     * @param string $className
     * @param array $args
     * @deprecated since version 1.0
     */
    public function createDataObject($className, array $args)
    {
        $badClass = !DataObject::isDataClassExists($className);

        if ($badClass) {
            if (class_exists($className)) {
                // var_dump(class_parents($className));
                $parents = class_parents($className);
                if (in_array("DAL\DataObject", $parents)) {
                    $dummy = new $className();
                } else {
                    throw new \Exception("Not a data object class name provided");
                }
            } else {
                throw new \Exception("Class not found");
            }
        }

        if (!in_array('id', $args)) {
            $args['id'] = uniqid('', true);  //\Login\Hasher::uniqueId(64);
            
        }

        $storableClassName = DataObject::getStorableFromClassName($className);

        $tbl = new \DAL\Table('dummy');
        $storableObject = $tbl->rowToObject($args, $storableClassName);
        return $storableObject->toDataObject();
    }

    public function isDataObjectExists($combinedId)
    {
        list($tableName, $id) = $combinedId;
        if (!$this->dal->isTableExists($tableName)) {
            return false;
        };
        $table = $this->dal->getTableByName($tableName);
        $rows = $table->selectRows("id = ?", [$id]);
        return (count($rows) >= 1);
    }
}
