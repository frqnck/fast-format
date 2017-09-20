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
 * Tokeniser.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class Tokeniser
{
    /**
     * Holds the default delimiters.
     * @var string
     */
    const OPEN  = '{',
          CLOSE = '}',
          DOT   = '.';

    /**
     * Holds the current (and default) delimiters.
     * @var string, string, string
     */
    protected $open  = self::OPEN,
              $close = self::CLOSE,
              $dot   = self::DOT;

    /**
     * Holds a token helper.
     * @Helper
     */
    protected $helper;

    /**
     * Holds the default regular expression to extract tokens upon.
     * @string
     */
    protected $sub_regex = '([a-zA-Z0-9_\.]+?)';

    /*
     * Holds the array of tokens (act also as a token cache).
     * @array
     */
    protected $tokens = array();

    /**
     * Constructor.
     *
     * @param Helper|null $helper
     */
    public function __construct(Helper $helper = null)
    {
        if(null !== $helper) {
            $this->helper = $helper->setTokeniser( $this );

            if( $this->helper->getFilterRegistry() !== null ) {
                // more flexible subpattern, allows non-alphanum characters.
                $sub = '([^' . preg_quote($this->open) . ']+?)';
                #$sub = '/([^'.$open.']+?)(?='.$close.')/', // twice slower
                #$sub = '/(?<='.$open.')([^'.$open.']+?)(?='.$close.')/', // tad faster

                $this->setSubRegex( $sub );
            }
        }
    }

    /**
     * Sets the subpattern regex to macth upon.
     *
     * Has to follows the PREG_PATTERN_ORDER as follow:
     *    0 => whole token key + delimiters, e.g. "{subj.det}" (full match)
     *    1 => just the token key, e.g. "subj.det" (subpattern match)
     *
     * @param string $format
     */
    public function setSubRegex($sub_regex)
    {
        $this->sub_regex = $sub_regex;
    }

    /**
     * Returns the regex to macth upon.
     *
     * @return string
     */
    public function getRegex()
    {
        $open = preg_quote($this->open);
        $close = preg_quote($this->close);

        return '/' . $open . $this->sub_regex . $close . '/';
    }

    /**
     * Sets the (token) delimiters.
     *
     * @param  string $open
     * @param  string $close
     * @param  string $separator
     * @return self
     */
    public function setDelimiters($open, $close, $separator = self::DOT)
    {
        $this->open  = $open ?: $this->open;
        $this->close = $close ?: $this->close;
        $this->dot   = $separator ?: $this->dot;

        return $this;
    }

    /**
     * Returns the named delimiter.
     *
     * @param  string $name
     * @return string
     */
    public function getDelimiter($name)
    {
        return $this->{$name};
    }

    /**
     * Sets the tokens.
     *
     * @param  array $tokens
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Returns all the tokens.
     *
     * @param  array $tokens
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Returns the token for the given key.
     *
     * @param  string        $key
     * @param  boolean|false $deep Seek deeper (if required).
     * @return string|null
     */
    public function getToken($key, $deep = false)
    {
        return isset($this->tokens[$key])
                ? $this->tokens[$key]
                : ($deep && @strpos($key, $this->dot, 1)
                    ? self::getByPath(
                        $key, $this->dot, $this->tokens
                    )
                    : null);
    }

    /**
     * Returns the token for the given path.
     *
     * @param  string $key
     * @param  string $dot The separator.
     * @param  mixed $val
     * @return string|null
     */
    static protected function getByPath($key, $dot, $val)
    {
        $keys = explode($dot, $key);

        while (1) {
            $key = array_shift($keys);
            if (!isset($key)) break;

            // $val = &$val[$key];
            // Avoid: "Indirect modification of overloaded element..
            $val = $val[$key]?:null;
        }

        return $val;
    }

    /**
     * Renders (and cache) the named token.
     *
     * @param  string $key
     * @return string
     */
    public function render($key)
    {
        $val = $this->getToken(strtolower($key), true);
        // $val = $this->getToken($key, true);

        if(null !== $this->helper) {
            $val = $this->helper->parse($key, $val);
        }

        return $this->tokens[$key] = $val; // cache the rendering
    }

    /**
     * Returns the named token, rendering it if required.
     *
     * @param  string $key
     * @return string
     */
    public function get($key)
    {
        $token = $this->getToken($key);
        return  $token!==null ? $token : $this->render($key);
    }

}