<?php

/**
 * This file is part of the Apix Pastis.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Filter;

/**
 * String
 * 
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class ArrayFilter
{

    public static function first($val)
    {
        return reset($val);
    }

    public static function last($val)
    {
        return end($val);
    }

    public static function join($val, $glue = ', ')
    {
        if((array) $val !== $val) {
            $val = StringFilter::split($val);
        }
        
        return implode($glue, (array) $val);
    }

}