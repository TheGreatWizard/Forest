<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of DummyStorable
 *
 * @author Guy
 */
class DummyStorable extends \DAL\Storable
{

    /**
     * @mysql_type VARCHAR(32)
     * @type string
     */
    public $name;

    /**
     * @mysql_type INT(3)
     * @type int
     */
    public $public_var;

    /**
     * @mysql_type INT(3)
     * @type int
     */
    public $private_var;

    /**
     * @mysql_type VARCHAR(32)
     * @type string
     */
    public $protected_var;

    /**
     * @mysql_type TEXT
     * @type array
     */
    public $public_array = array("one", "two", "three"); //put your code here

    public function toDataObject(){
        $do = new \DAL\DummyNotStorableDataObject();
        $dc = new DummyClass();
        $dc->public_var = $this->public_var;
        $dc->public_array = $this->public_array;
        $dc->setPrivateVar($this->private_var);
        $dc->setProtectedVar($this->protected_var);
        $do->dummyClass = $dc;
        $do->name =  $this->name;
        return $do;
    }
}
