<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of DummyStorableWithMultipleConnectors
 *
 * @author Guy
 */
class DummyStorableWithMultipleConnectors extends StorableWithMultipleConnectors
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

    
    
    public function toDataObject(){
        $do = new \DAL\DummyDataWithMultipleConnectors();
        $do->setId($this->id); 
        $do->simpleArray = $this->simpleArray;
        $do->integerValue = $this->integerValue;
        $do->stringValue = $this->stringValue;
        foreach ($this->ids as $id) {
            $do->addEmptyConnection($id);
        }
        return $do;
    }

}
