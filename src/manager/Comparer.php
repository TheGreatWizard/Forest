<?php

namespace Manager;

/**
 * Singelton, used to compare objects and thier fields.
 */
class Comparer
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Comparer();
        }
        return self::$instance;
    }

    /**
     * Comparing all the properties of the object
     */
    public function areEqual($obj1, $obj2)
    {

        // Same instance of the same class
        if ($obj1 === $obj2) {
            return true;
        }

        // Different clases
        $class1 = get_class($obj1);
        $class2 = get_class($obj2);
        if ($class1 !== $class2) {
            return false;
        }

        // Instantiate the reflection object
        $reflector = new \ReflectionClass($class1);

        // Now get all the properties from class A in to $properties array
        $properties = $reflector->getProperties();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $val1 = $property->getValue($obj1);
            $val2 = $property->getValue($obj2);

            if (is_array($val1)) {
                if (!(is_array($val2))) {
                    return false;
                }

                if (count($val1) !== count($val2)) {
                    return false;
                }

                foreach ($val1 as $key => $val) {
                    if (!array_key_exists($key, $val2)) {
                        return false;
                    }
                    if (!$this->areEqual($val1 [$key], $val2 [$key])) {
                        return false;
                    }
                }
            } elseif (is_object($val1)) {
                if (!(is_object($val2))) {
                    return false;
                }
                return $this->areEqual($val1, $val2);
            } elseif ($val1 !== $val2) {
                return false;
            }
        } // foreach

        return true;
    }
// function areEqual
}
