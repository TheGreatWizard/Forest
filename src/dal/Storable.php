<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 *
 * @author Guy
 */
abstract class Storable
{
     /**
     * unique id of the object
     * @mysql_type VARCHAR(64) UNIQUE
     * @type string
     */
    public $id = "";

    abstract public function toDataObject();
}
