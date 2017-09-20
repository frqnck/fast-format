<?php

/**
 * This file is part of the Apix Pastis.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String;

use Apix\String\Helper\Adapter as Helper;

/**
 * String formatter.
 *
 * Replaces placeholders in strings with nested array values.
 *
 * Here's an example of usage:
 * <code>
 *      $f = new Format;
 *      $f->parse("A {bad} example has {some.silly} monkeys...");
 *
 *      echo $f->render(['bad' => 'good', 'some' => ['silly' => 'crazy']);
 * </code>
 *
 * Prints "A good example has crazy monkeys..."
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class Format
{
    /**
     * Holds the output format.
     * @var string
     */
    protected $format;

    /**
     * Holds the extracted token keys from the format string.
     * @var array
     */
    protected $keys = [];

    /**
     * Holds a tokeniser.
     * @Tokeniser
     */
    protected $tokeniser;

    /**
     * Constructor.
     *
     * @param Tokeniser $tokeniser
     */
    // public function __construct(Tokeniser $tokeniser = null)
    // {
    //     $this->tokeniser = $tokeniser ?: new Tokeniser;
    // }

    public function __construct(Helper $helper = null)
    {
        $this->tokeniser = new Tokeniser( $helper );
    }

    /**
     * Parses the given format/template string.
     *
     * @param  string $format
     * @return self
     */
    public function parse($format)
    {
        preg_match_all(
            $this->tokeniser->getRegex(),
            $this->format = (string) $format,
            $this->keys
        );

        return $this;
    }

    /**
     * Returns the provided tokens as a formatted string.
     *
     * @param  array $tokens
     * @return string
     */
    public function render(array $tokens = null)
    {
        if($tokens)
            $this->tokeniser->setTokens($tokens);

        return (string) $this;
    }

    /**
     * Returns a formatted string.
     *
     * @return string
     */
    public function __toString()
    {
        $out = $this->format;

        foreach($this->keys[1] as $i => $key) {
            $val = $this->tokeniser->get($key);

            if(is_scalar($val)) {
                $out = str_replace(
                    $this->keys[0][$i], $val, $out // slower!
                    // $this->open . $key . $this->close, $val, $out
                );
            }
        }

        return $out;
    }

    /**
     * Returns the current tokeniser.
     *
     * @return Tokeniser
     */
    public function getTokeniser()
    {
        return $this->tokeniser;
    }

}