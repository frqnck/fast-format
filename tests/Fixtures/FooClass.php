<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Fixtures;

class FooClass
{
    public function instanceFilter($value)
    {
        return $value;
    }

    static public function staticFilter($value = 'string')
    {
        return $value;
    }

}