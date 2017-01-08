<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of StorableWithMultipleConnectors
 *
 * @author Guy
 */
abstract class StorableWithMultipleConnectors  extends Storable
{
    /**
     * @mysql_type TEXT
     * @type array 
     */
    public $ids;
}
