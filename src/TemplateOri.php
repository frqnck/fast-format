<?php

/**
 * This file is part of the Apix Pastis.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String;

use Psr\Log;
use Apix\String\Helper\Adapter as Helper;

/**
 * String Templater.
 *
 * Replace placeholders in strings with nested (array) values.
 *
 * Example:
 * <code>
 * $engine->render('{foo} likes {bar.0} and {bar.1}', ['a' => 'b', 'c' => ['d', 'e']]);
 * // pPrints "This is b and these are d and e"
 * </code>
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class TemplateOri implements Log\LoggerAwareInterface
{
    /**
     * Holds the default delimiters.
     * @var string
     */
    const OPEN  = '{',
          CLOSE = '}',
          DOT   = '.';

    /**
     * Holds the open delimiter.
     * @var string
     */
    protected $open = self::OPEN;

    /**
     * Holds the right delimiter.
     * @var string
     */
    protected $close = self::CLOSE;

    /**
     * Holds the token separator.
     * @var string
     */
    protected $dot = self::DOT;

    /**
     * Holds the output format.
     * @var string
     */
    protected $format;

    /**
     * Holds the extracted keys/tokens from the format string.
     * @var array
     */    
    protected $keys;

    /*
     * Holds the array of tokens (act also as a token cache). 
     * @array 
     */
    protected $tokens;

    /**
     * Holds a PSR3 compliant logger, e.g. apix-log
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Holds a token helper.
     * @var Helper|null
     */
    protected $helper = null;

    /**
     * Constructor.
     *
     * @param Helper $helper
     */
    public function __construct(Helper $helper = null)
    {
        if($helper) {
            $this->setHelper($helper);
        }
    
        $this->setLogger(new Log\NullLogger);
    }

    /**
     * Sets the helper.
     *
     * @param  Helper $helper
     * @return self
     */
    public function setHelper(Helper $helper)
    {
        $this->helper = $helper->setTemplate($this);

        return $this;
    }

    public function getHelper($helper_name = null)
    {
        return null !== $helper_name
            ? $this->helper->get($helper_name)
            : $this->helper;
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
        $this->open = $open ?: $this->open;
        $this->close = $close ?: $this->close;
        $this->dot = $separator ?: $this->dot;

        return $this;
    }

    /**
     * Parses (pre-processes) the given format/template string.
     *
     * @param  string $format
     * @return self
     */
    public function setFormat($format)
    {
        $this->format = (string) $format;

        $open = preg_quote($this->open);
        $close = preg_quote($this->close);
        preg_match_all(
            // '/([^'.$left.']+?)(?='. $right .')/', // tad slower?
            '/(?<=' . $open . ')([^'. $open .']+?)(?=' . $close . ')/', // tad faster?
            $this->format,
            $this->keys
        );

        return $this;
    }

    /**
     * Parses the given format/template string.
     *
     * @param  string $format
     * @return self
     */
    public function parse($format)
    {
        return $this->setFormat($format);
    }

    /**
     * Loads and parses the given filename.
     *
     * @param  string $filename
     * @throws \InvalidArgumentException
     */
    public function parseFile($filename)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Template file "%s" could not be loaded',
                    $filename
                )
            );
        }

        return $this->setFormat(file_get_contents($filename));
    }

    /**
     * Sets the given array or object as templating tokens.
     *
     * @param  array|object $tokens
     * @param  boolean      $flatten Wether to flatten multi-dimensional tokens  upfront. May speed-up lookup time with (very) large set of tokens.
     *                         upfront. May speed-up lookup time with (very)
     *                         large set of tokens.
     * @return self
     */
    public function setTokens($tokens, $flatten = false)
    {
        if($tokens !== (array) $tokens) {
            if(is_object($tokens)) {
                if(method_exists($tokens, 'toArray') ) {
                    $tokens = $tokens->toArray();
                } else {
                    $tokens = json_decode(json_encode($tokens), true);
                }
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'First argument to "%s" must be of type array or object, %s given',
                        __METHOD__, gettype($tokens)
                    )
                );
            }            
        }

        if($flatten) {
            $tokens = self::flatten($tokens, $this->dot);
        }

        $this->tokens = $tokens;

        return $this;
    }

    public function setObjectTokens($tokens)
    {
        $tokens = new \RecursiveArrayIterator($tokens);

        $this->tokens = $tokens;

        // var_dump($tokens['sys']['php_version']); exit;
    }


    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Flatten a multi-dimensional associative array with the given separator.
     *
     * @param  array  $array
     * @param  string $separator
     * @param  string $prepend
     * @return array
     */
    public static function flatten($array, $separator, $prepend = '')
    {
        $results = array();
        foreach ($array as $key => $val) {
            if ($val === (array) $val) {
                $results = array_merge(
                    $results,
                    self::flatten(
                        $val,
                        $separator,
                        $prepend . $key . $separator
                    )
                );
            } else {
                $results[$prepend . $key] = $val;
            }
        }

        return $results;
    }

    /**
     * Returns the token for the given key.
     *
     * @param  string        $key
     * @param  boolean|false $deep Seek deeper (only if required). 
     * @return string|null
     */
    public function getToken($key, $deep = false)
    {
        return isset($this->tokens[$key])
                ? $this->tokens[$key]
                : ($deep && @strpos($key, $this->dot, 1)
                    ? self::getTokenByPath(
                        $key, $this->dot, $this->tokens
                    )
                    : null);
    }

    /**
     * Returns the token for the given path.
     *
     * @param  string $key
     * @return string|null
     */
    static protected function getTokenByPath(&$key, $separator, $val)
    {
        $keys = explode($separator, $key);

        // while ($key = array_shift($keys)) {
        while (1) {
            $key = array_shift($keys);
            if (!isset($key)) { break; 
            } 

            // PHP Notice: Indirect modification of overloaded element...
            // Fatal error: Cannot create references to/from string offsets...
            $val = &$val[$key];

            // $val = &$val->$key;
            // $val = $val[$key]; // !!!
        }

        return $val;
    }

    /**
     * Renders the named token.
     *
     * @param  string $key
     * @return string
     */
    public function renderToken($key)
    {
        // $val = $this->getToken(strtolower($key), true);
        $val = $this->getToken($key, true);
        
        if($this->helper) {
            $val = $this->helper->parse($key, $val);
        }

        return $val;
    }
    
    /**
     * Returns a formatted string.
     *
     * @return string
     */
    public function __toString()
    {
        $out = $this->format;
        foreach($this->keys[0] as $key) {
            try {
                $val = $this->getToken($key)
                   ? : $this->tokens[$key] = $this->renderToken($key);

                if(is_scalar($val)) {
                    $out = str_replace(
                        $this->open . $key . $this->close,
                        $val,
                        $out
                    );
                } else {
                    throw new \LogicException(
                        sprintf(
                            'Token "%s" is undefined',
                            $this->open . $key . $this->close
                        )
                    );
                }
            } catch(\Exception $e) {
                $this->logger->warning(
                    '{0} -- {1}', array(__CLASS__, $e->getMessage(),
                    )
                );
            }
        }
 
        return $out;
    }

    /**
     * Returns the provided tokens as a formatted string.
     *
     * @param  array|object $tokens
     * @return string
     */
    public function render($tokens)
    {
        return (string) $this->setTokens($tokens);
    }

    /**
     * Returns the tokens as according to the provide formatted string.
     *
     * @param  string       $format
     * @param  array|object $tokens
     * @return string
     */
    public function formatRender($format, $tokens)
    {
        return (string) $this->setFormat($format)->setTokens($tokens);
    }

    /**
     * Sets a logger.
     * 
     * @param  Log\LoggerInterface $logger
     * @return self
     */
    public function setLogger(Log\LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

}