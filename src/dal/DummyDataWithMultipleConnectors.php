<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of DummyDataWithMultipleConnectors
 *
 * @author Guy
 */
class DummyDataWithMultipleConnectors extends \DAL\DataWithMultipleConnectors
{

    /**
     * @mysql_type TEXT
     * @type array 
     */
    public $simpleArray;

    /**
     * @mysql_type INT(3)
     * @type int
     */
    public $integerValue;

    /**
     * @mysql_type VARCHAR(32)
     * @type string
     */
    public $stringValue;

    /**
     * @mysql_type donotsave 
     */
    public $donotsave;

    /**
     * @mysql_type donotsave 
     */
    public $innerDataObjects = [];

    /**
     * @mysql_type donotsave 
     */
    private $index;

    public function __construct()
    {
        parent::__construct();
        $this->simpleArray = ['text', 77];
        $this->integerValue = 102;
        $this->stringValue = 'some string';
        $this->index = 0;
    }

    public function addEmptyConnection($key)
    {
        $c = new \DAL\Connector();
        $c->data = null;
        $c->dataCombinedId = $key;
        $this->innerDataObjects[] = $c;
    }
    
    public function addConnection($data)
    {
        $c = new \DAL\Connector();
        $c->data = $data;
        $c->dataCombinedId = $data->getCombinedId();
        $this->innerDataObjects[] = $c;
    }

    public function getStorableClassName()
    {
        return "\DAL\DummyStorableWithMultipleConnectors";
    }

    public function toStorable()
    {
        $st = parent::toStorable();
        $st->simpleArray = $this->simpleArray;
        $st->integerValue = $this->integerValue;
        $st->stringValue = $this->stringValue;
        $st->ids = array_map(function($x){return $x->dataCombinedId;},$this->innerDataObjects);
        return $st;
    }

    public function setCurrent($value)
    {
        $this->innerDataObjects[$this->index]->data = $value;
    }

    public function setKey($value)
    {
        $this->innerDataObjects[$this->index]->dataCombinedId = $value;
        $this->innerDataObjects[$this->index]->data->setCombinedId($value);
    }

    public function current()
    {
        return $this->innerDataObjects[$this->index]->data;
    }

    public function key()
    {
        return $this->innerDataObjects[$this->index]->dataCombinedId;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function next()
    {
        $this->index += 1;
    }

    public function valid()
    {
        return $this->index < count($this->innerDataObjects);
    }

    public function cloneWithoutData()
    {
        
    }
}
