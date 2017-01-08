<?php

namespace DAL;

/*
 * https://dev.mysql.com/doc/refman/5.0/en/data-types.html
 */

class Field
{
    private $name;
    private $type;
    private $zerofill = false;
    private $unsigned = false;
    private $auto_increment = false;
    private $primary_key = false;
    private $null = true;
    private $unique = false;
    public $invalid = true;

    public function __construct($name = '', $type = '', $lenght_values = '')
    {
        if ($name !== '') {
            $this->setType($type, $lenght_values);
            $this->setName($name);
            $this->invalid = false;
        }
    }

    private static $ModifiersList = array(
        //Numeric
        array("AUTO_INCREMENT", "NOT NULL", "UNIQUE", "PRIMARY KEY"),
        //Numeric2
        array("ZEROFILL", "SIGNED", "UNSIGNED"),
        //Text
        array("CHARACTER SET", "COLLATE", "CHARSET", "ASCII", "UNICODE", "BINARY")
    );
    private static $FieldTypes = array(
        "CHAR" => array("values" => array(1), "modifiers" => array()),
        "VARCHAR" => array("values" => array(1), "modifiers" => array()),
        "TEXT" => array("values" => array(0, 1), "modifiers" => array()),
        "DATETIME" => array("values" => array(0, 1), "modifiers" => array()),
        "BIT" => array("values" => array(0, 1), "modifiers" => array(0)),
        "TINYINT" => array("values" => array(0, 1), "modifiers" => array(0, 1)),
        "BOOL" => array("values" => array(0, 1), "modifiers" => array()),
        "BOOLEAN" => array("values" => array(0, 1), "modifiers" => array()),
        "SMALLINT" => array("values" => array(0, 1), "modifiers" => array(0, 1)),
        "MEDIUMINT" => array("values" => array(0, 1), "modifiers" => array(0, 1)),
        "INT" => array("values" => array(0, 1), "modifiers" => array(0, 1)),
        "INTEGER" => array("values" => array(0, 1), "modifiers" => array(0, 1)),
        "BIGINT" => array("values" => array(0, 1), "modifiers" => array(0, 1)),
        "SERIAL" => array("values" => array(0, 1), "modifiers" => array()),
        "DECIMAL" => array("values" => array(0, 1, 2), "modifiers" => array(0, 1)),
        "DEC" => array("values" => array(0, 1, 2), "modifiers" => array(0, 1)),
        "NUMERIC" => array("values" => array(0, 1, 2), "modifiers" => array(0, 1)),
        "FIXED" => array("values" => array(0, 1, 2), "modifiers" => array(0, 1)),
        "FLOAT" => array("values" => array(0, 2), "modifiers" => array(0, 1)),
        "DOUBLE" => array("values" => array(0, 2), "modifiers" => array(0, 1)),
        "DOUBLE PRECISION" => array("values" => array(0, 2), "modifiers" => array(0, 1)),
        "REAL" => array("values" => array(0, 2), "modifiers" => array(0, 1)),
    );

    public function getAutoIncrement()
    {
        return $this->auto_increment;
    }

    public function getPrimaryKey()
    {
        return $this->primary_key;
    }

    public function getNull()
    {
        return $this->null;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        if (!preg_match('/[^A-Za-z0-9_]/', $name)) {
            // string contains only english letters & digits
            $this->name = $name;
        } else {
            throw new \Exception('Bad field name. Field name can contain only english letters & digits');
        }
    }

    public function getType()
    {
        return $this->type;
    }
    /*
      //Numeric
      array("AUTO_INCREMENT", "NOT NULL", "UNIQUE", "PRIMARY KEY"),
      //Numeric2
      array("ZEROFILL", "SIGNED", "UNSIGNED"),
      //Text
      array("CHARACTER SET", "COLLATE", "CHARSET", "ASCII", "UNICODE", "BINARY")
     */

    public function setFromMysqlDesc($arr)
    {
        //var_dump($arr);
        $this->setName($arr['Field']);
        $this->setType($arr['Type']);

        if ($arr['Null'] === "NO") {
            $this->null = false;
        } else {
            //$this->type .= " NULL";
            $this->null = true;
        }


        if ($arr['Extra'] === "auto_increment") {
            //$this->type .= " AUTO_INCREMENT";
            $this->auto_increment = true;
        } else {
            $this->auto_increment = false;
        }

        if ($arr['Key'] === "PRI") {
            //$this->type .= " PRIMARY KEY";
            $this->primary_key = true;
        } else {
            $this->primary_key = false;
        }

        $this->invalid = false;
    }

    public function setFromKeyAndVal($key, $val)
    {
        $this->setName($key);

        $val_type = gettype($val);

        if ($val_type == "integer") {
            $this->setType('INT(6) ZEROFILL');
        } elseif ($val_type == "string") {
            $this->setType('VARCHAR(30)');
        } else {
            $this->setType('TEXT');
        }
    }

//  function setFromKeyAndVal($key,$val)

    public function getSQL()
    {
        $str = $this->getName() . " " . $this->getType();

        //$unsigned = false,
        if ($this->auto_increment) {
            $str .= ' AUTO_INCREMENT';
        }

        if ($this->primary_key) {
            $str .= ' PRIMARY KEY';
        }

        if (!$this->null) {
            $str .= ' NOT NULL';
        }

        return $str;
    }

    //private function updateType(){
    //}

    private function updateModifiers($modifiers)
    {
        if (count($modifiers) == 0) {
            return true;
        }
        $modifier = array_shift($modifiers);

        switch ($modifier) {
            case 'ZEROFILL':
                if ($this->unsigned) {
                    $this->type = str_replace(" UNSIGNED", "", $this->type);
                }
                $this->type .= ' ZEROFILL UNSIGNED';
                $this->zerofill = true;
                $this->unsigned = true;
            // no break, contains UNSIGNED case
            case 'UNSIGNED':
                if (!$this->zerofill) {
                    $this->type .= ' UNSIGNED';
                    $this->unsigned = true;
                }
                break;
            case 'AUTO_INCREMENT':
                //$this->type .= ' AUTO_INCREMENT';
                $this->auto_increment = true;
                $this->null = false;
                break;
            case 'UNIQUE':
                $this->type .= ' UNIQUE';
                $this->unique = true;
                break;
            case 'PRIMARY':
                $modifier = array_shift($modifiers);
                if ($modifier == 'KEY') {
                    //$this->type .= ' PRIMARY KEY';
                    $this->primary_key = true;
                }
                break;
            case 'NOT':
                $modifier = array_shift($modifiers);
                if ($modifier == 'NULL') {
                    //$this->type .= ' NOT NULL';
                    $this->null = false;
                }
                break;
            default:
                return false;
        }

        return $modifiers;
    }

    private function extractValuesFromString($str)
    {
        $values = array();

        preg_match('(\(\d+\))', $str, $single_number);
        if (count($single_number) > 0) {
            $values[] = (int) substr($single_number[0], 1, -1);
        } else {
            preg_match('(\(\d+,\d+\))', $str, $two_numbers);
            if (count($two_numbers) > 0) {
                $two_numbers = substr($two_numbers[0], 1, -1);
                $numbers = explode(",", $two_numbers);
                $values[] = (int) $numbers[0];
                $values[] = (int) $numbers[1];
            }
        }

        return $values;
    }

//function extractValuesFromString

    /*
     * @todo: complete sql data type wrapper
     */

    public function setType($type, $lenght_values = "")
    {
        $type = strtoupper($type);
        $parts = explode(' ', $type);
        $type_name = array_shift($parts);
        $correct_type = false;


        $values = array();
        // extract lenght_values from $type_name string

        $values_data = "(" . $lenght_values . ")";
        if ($values_data === "()") {
            $values_data = $type_name;
            $type_name_parts = explode('(', $type_name);
            $type_name = $type_name_parts[0];
        }

        $values = $this->extractValuesFromString($values_data);
        if (count($values) > 0) {
            $lenght_values = (string) $values[0];
            if (count($values) > 1) {
                $lenght_values .= ",{$values[1]}";
            }
        }


        $correct_type = array_key_exists($type_name, self::$FieldTypes);
        if (!$correct_type) {
            throw new \Exception("Field type is not recognized:" . $type_name);
        }

        $type_def = self::$FieldTypes[$type_name];

        $correct_type = in_array(count($values), $type_def["values"]);
        if (!$correct_type) {
            throw new Exception("Incorrect number of lenght/values provided " . (string) count($values));
        }

        //echo "$type_name = {$type_name}";

        $this->type = $type_name;

        if (count($values) > 0) {
            $this->type .= "(" . $lenght_values . ")";
        }


        $more_parts = (count($parts) > 0);

        while ($correct_type && $more_parts) {
            $parts = $this->updateModifiers($parts);
            if ($parts === false) {
                $correct_type = false;
                throw new Exception("Field type modifier is not recognized");
            } elseif (count($parts) == 0) {
                $more_parts = false;
                //echo "Finshed";
            }
        }


        //echo "\n>>>". $this->type;
        //echo "\n";
        //echo ($correct_type) ? 'true' : 'false';
        if (!$correct_type) {
            throw new Exception("Field type is not recognized");
        }
    }
}
