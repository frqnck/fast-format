<?php

/**
 * This file is part of the Apix Pastis.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

use Apix\String\Tokeniser;

/**
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
abstract class AbstractHelper implements Adapter
{

    /**
     * Holds a tokeniser.
     * @var Tokeniser
     */
    protected $tokeniser;

    /**
     * Holds the filter registry.
     * @var FilterRegistry
     */
    protected $filter_registry;

    /**
     * Sets a tokeniser.
     *
     * @param  Tokeniser $tokeniser
     * @return self
     */
    public function setTokeniser(Tokeniser $tokeniser)
    {
        $this->tokeniser = $tokeniser;

        return $this;
    }

    /**
     * Sets the filter registry.
     *
     * @param  FilterRegistry $filter_registry
     * @return self
     */
    public function setFilterRegistry(FilterRegistry $filter_registry)
    {
        $this->filter_registry = $filter_registry;

        return $this;
    }

    /**
     * Returns the filter registry.
     *
     * @return FilterRegistry
     */
    public function getFilterRegistry()
    {
        return $this->filter_registry;
    }

}