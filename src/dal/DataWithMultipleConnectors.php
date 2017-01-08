<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of DataWithMultipleConnectors
 *
 * @author Guy
 */
abstract class DataWithMultipleConnectors extends DataObject implements \Iterator
{

    abstract public function setCurrent($data);

    abstract public function setKey($key);

    abstract public function addEmptyConnection($key);

    abstract public function addConnection($data);
    
    abstract public function cloneWithoutData();

    /**
     * @mysql_type donotsave 
     */
    public $dataType = 2;

    public function __construct()
    {
        parent::__construct();
    }

    public function toStorable()
    {
        $storableObjectClassName = $this->getStorableClassName();
        if (get_class($this) == $storableObjectClassName) {
            return $this;
        }

        $st = parent::toStorable();
        return $st;
    }
}
