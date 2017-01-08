<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of DataWithSingleConnector
 *
 * @author Guy
 */
abstract class DataWithSingleConnector extends DataObject
{

    /**
     * @mysql_type VARCHAR(64) UNIQUE
     * @type string
     */
    private $dataId = "";

    /**
     * @mysql_type VARCHAR(64)
     * @type string
     */
    private $dataClass = "";

    /**
     * @mysql_type donotsave 
     */
    public $data;

    public function connectData($data)
    {
        list($this->dataClass, $this->dataId) = $data->getCombinedId();
        $this->data = $data;
    }

    public function addEmptyConnection($key)
    {
        list($this->dataClass, $this->dataId) = $key;
        $this->data = null;
    }

    public function detachData()
    {
        $this->data = null;
    }

    /**
     * @mysql_type donotsave 
     */
    public $dataType = 1;

    public function __construct()
    {
        parent::__construct();
    }

    public function getDataCombinedId()
    {
        return [$this->dataClass, $this->dataId];
    }

    public function setDataCombinedId($cid)
    {
        list($this->dataClass, $this->dataId) = $cid;
    }
    
    public function toStorable()
    {
        $storableObjectClassName = $this->getStorableClassName();
        if (get_class($this) == $storableObjectClassName) {
            return $this;
        }


        $st = parent::toStorable();
        $st->innerDataCombinedId = $this->getDataCombinedId();
        return $st;
    }
}
