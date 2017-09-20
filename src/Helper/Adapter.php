<?php

/**
 * This file is part of the Apix Pastis.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

/**
 * Describes a helper.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
interface Adapter
{

    /**
     * Parses the given token and applies the given value.
     *
     * @param  string $token
     * @param  string $value
     * @return mixed
     */
    public function parse($token, $value);

}