<?php

namespace DAL;

class DummyClass extends DummyParentClass
{

    
    /**
     * @mysql_type INT(3)
     * @type int
     */
    public $id = 1;

    /**
     * @mysql_type INT(3)
     * @type int
     */
    public $public_var = 10;

    /**
     * @mysql_type INT(3)
     * @type int
     */
    private $private_var = 11;

    public function getPrivateVar()
    {
        return $this->private_var;
    }

    public function setPrivateVar($value)
    {
        $this->private_var = $value;
    }

    /**
     * @mysql_type TEXT
     * @type array
     */
    public $public_array = array("one", "two", "three");

}
