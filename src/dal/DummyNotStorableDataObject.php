<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of DummyNotStorableDataObject
 *
 * @author Guy
 */
class DummyNotStorableDataObject extends DataObject
{
    public $name;
    public $dummyClass;

    public function __construct()
    {
        parent::__construct();
        $this->dummyClass = new DummyClass();
        $this->name = "unknown";
    }
    
    public function toStorable()
    {
        $st = parent::toStorable();
        $st->name = $this->name;
        $st->public_var = $this->dummyClass->public_var;
        $st->private_var = $this->dummyClass->getPrivateVar();
        $st->protected_var = $this->dummyClass->getProtectedVar();
        $st->public_array = $this->dummyClass->public_array;
        return $st;
    }
    
    public function getStorableClassName()
    {
        return "\DAL\DummyStorable";
        
    }
}
