<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of DummyDataObject
 *
 * @author Guy
 */
class DummyDataObject extends DataObject
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
     
    public function __construct()
    {
        parent::__construct();
        $this->simpleArray = ['dummydata', 101];
        $this->integerValue = 188;
        $this->stringValue = 'some dummy string';
        $this->donotsave = "Dont save this";
    }
}
