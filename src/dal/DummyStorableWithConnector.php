<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of DummyStorableWithConnector
 *
 * @author Guy
 */
class DummyStorableWithConnector extends \DAL\StorableWithSingleConnector
{

    /**
     * @mysql_type VARCHAR(32)
     * @type string
     */
    public $somedata;
    
    public function toDataObject()
    {
        $d = new DummyNotStorableWithConnector();
        $d->setId($this->id);
        $d->somedata = $this->somedata;
        return $d;
    }

}
