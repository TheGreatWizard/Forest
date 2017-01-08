<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of DummyNotStorableWithConnector
 *
 * @author Guy
 */
class DummyNotStorableWithConnector extends \DAL\DataWithSingleConnector
{
    public $somedata;
    public function __construct()
    {
        parent::__construct();
        $this->somedata = "Some data, 220";
    }
    public function getStorableClassName()
    {
        return "\DAL\DummyStorableWithConnector";
    }
    public function toStorable()
    {
       $st = parent::toStorable();
       $st->somedata = $this->somedata;
       return $st; 
    }
}
