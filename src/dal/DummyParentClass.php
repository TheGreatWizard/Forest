<?php

namespace DAL;

class DummyParentClass
{


    /**
     * @mysql_type VARCHAR(32)
     * @type string
     */
    protected $protected_var = "kuzimuzi";

    public function getProtectedVar()
    {
        return $this->protected_var;
    }

    public function setProtectedVar($value)
    {
        $this->protected_var = $value;
    }

}
