<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of DummyInnerData
 *
 * @author Guy
 */
class DummyInnerData extends DataObject
{
    public $complexData = "Complex";
    //put your code here
     public function getStorableClassName()
    {
        return "\DAL\DummyInnerData";
    }
    
    public function __construct()
    {
        parent::__construct();
    }
    
}
