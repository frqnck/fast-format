<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String;

/**
 * String formatter with `sprintf` conversion specifier.
 *
 * Replaces placeholders in strings with nested array values,
 * optionally converting specifier (%) after the placeholder name.
 *
 * Here's an example of usage:
 * <code>
 *      $f = new Sprintf;
 *      $f->parse("A {bad} example has {some.silly%01.4f} monkeys...");
 *
 *      echo $f->render(['bad' => 'good', 'some' => ['silly' => 100]);
 * </code>
 *
 * Prints "A good example has 100.000 monkeys..."
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class Sprintf extends Format
{

    /**
     * Constructor.
     *
     * @param Tokeniser $tokeniser
     */
    public function __construct(Tokeniser $tokeniser = null)
    {
        $this->tokeniser = $tokeniser ?: new Tokeniser;

        $this->open = $this->tokeniser->getDelimiter('open');
        $this->close = $this->tokeniser->getDelimiter('close');
    }

    /**
     * {@inheritdoc}
     *
     * The parsed keys are:
     *    0 => whole token key  e.g. "{subj.det%s}"
     *    1 => just array key   e.g. "subj.det"
     *    2 => the sprintf idx  e.g. "%s"
     */
    public function parse($format)
    {
        $open = '(?:'. preg_quote($this->open) . ')';
        $open .= '*' . $open;

        $close = '(?:' . preg_quote($this->close) . ')';
        $close .= $close . '*';

        preg_match_all(
            '/' . $open . '(.*?)(%[^' . $this->close . ']+)?' . $close . '/',
            $this->format = (string) $format,
            $this->keys
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $out = $this->format;
        foreach($this->keys[1] as $i => $key) {

            $val = $this->tokeniser->get($key);

            // if(strpos($this->keys[2][$i], '%') !== false) {
            if($this->keys[2][$i] !== '') {
                $val = sprintf($this->keys[2][$i], $val);
            }

            if(is_scalar($val)) {
                $out = str_replace($this->keys[0][$i], $val, $out);
            }
        }

        return $out;
    }

}