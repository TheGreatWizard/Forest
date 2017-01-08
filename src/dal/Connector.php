<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of Connector
 * @author Guy
 */
class Connector
{
    public $dataCombinedId;
    public $data;
    
    public function connectData($data){
        $this->dataCombinedId = $data->getCombinedId();
        $this->data = $data;
    }
    
    public function addEmptyConnection($key){
        $this->dataCombinedId = $key;
        $this->data = null;
    }
    
    public function detachData(){
        $this->data = null;
    }
}
