<?php

/**
 * This file is part of the Apix Pastis.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

use Apix\String\Filter;

/**
 * The Logic....
 * 
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class LogicHelper extends LogicParser implements Adapter
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $registry = new FilterRegistry;
        
        // $registry
        //     ->inject(new Filter\EscapeFilter);
        
        parent::__construct($registry);
    }

}