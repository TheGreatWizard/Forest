<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of Reflection
 *
 * @author Guy
 */
class Reflection
{
    /**
     * @param string $className
     * @returns array of object \ReflectionProperty.
     */
    public static function getClassProperties($className, $filter = null)
    {
        $ref = new \ReflectionClass($className);
        if ($filter == null) {
            $props = $ref->getProperties();
        } else {
            $props = $ref->getProperties($filter);
        }
        if ($parentClass = $ref->getParentClass()) {
            $parent_private_props_arr = self::getClassProperties($parentClass->getName(), \ReflectionProperty::IS_PRIVATE); // RECURSION
            if (count($parent_private_props_arr) > 0) {
                $props = array_merge($parent_private_props_arr, $props);
            }
        }

        return $props;
    }
    
    /**
     * Extracts the type of a property from DocComment, using type tag
     * @param \ReflectionProperty $prop
     */
    public static function getTypeFromProperty($prop)
    {
        $comment_string = $prop->getDocComment();
        //define the regular expression pattern to use for string matching
        $pattern = "#(@type+\s*[a-zA-Z0-9, ()_].*)#";

        //perform the regular expression on the string provided
        preg_match_all($pattern, $comment_string, $matches, PREG_PATTERN_ORDER);

        if (count($matches[0]) > 0) {
            $split = explode(' ', $matches [0] [0]);
            if (count($split) > 1) {
                $propType = $split[1];
            } else {
                $propType = "string";
            }
        } else {
            $propType = "string";
        }

        return $propType;
    }
    
     /**
     * Extracts the mysql field type of a property from DocComment, using mysql_type tag
     * @param \ReflectionProperty $prop
     */
    public static function getMySqlTypeFromProperty($prop)
    {
        $comment_string = $prop->getDocComment();
        //define the regular expression pattern to use for string matching
        $pattern = "#(@mysql_type+\s*[a-zA-Z0-9, ()_].*)#";

        //perform the regular expression on the string provided
        preg_match_all($pattern, $comment_string, $matches, PREG_PATTERN_ORDER);
        if (count($matches[0]) > 0) {
            $propType = trim(str_replace("@mysql_type ", "", $matches [0] [0]));
        } else {
            $propType = "TEXT";
        }

        return $propType;
    }
    
    
}
