<?php

/**
 * This file is part of the Apix Pastis.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Filter;

use Aura\Html;

/**
 * Escaper.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class EscapeFilter
{

    public function __construct()
    {
        $escaper_factory = new Html\EscaperFactory;
        $escaper = $escaper_factory->newInstance();
        Html\Escaper::setStatic($escaper);

        return $escaper;
    }

}