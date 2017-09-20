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
 * Factory to create decorated Template objects.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class Template implements Log\LoggerAwareInterface
{
    /**
     * Holds a PSR3 compliant logger, e.g. apix-log
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param Helper $helper
     * @param Formatter $formatter
     */
    public function __construct(Helper $helper = null)
    {
        // $this->tokeniser = new Tokeniser($helper);
        // $this->formatter = new Format($this->tokeniser);
        $this->formatter = new Format($helper);

        // The logger is used to log Exceptions.
        $this->setLogger( new Log\NullLogger );
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

        return $this->parse(file_get_contents($filename));
    }

    /**
     * Parses the given format/template string.
     *
     * @param  string $format
     * @return self
     */
    public function parse($format)
    {
        $this->formatter->parse($format);

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
        return $this->formatter->render( $tokens );
    }

    /**
     * Returns the provided formatted string rendered with the given tokens.
     *
     * @param  string       $format
     * @param  array|object $tokens
     * @return string
     */
    public function formatRender($format, array $tokens)
    {
        return $this->formatter->parse( $format )->render( $tokens );
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
    public function setTokens($data, $flatten = false)
    {

        if($data !== (array) $data) {
            if(is_object($data)) {
                // if(method_exists($data, 'toArray') ) {
                //     $data = $data->toArray();
                // } else {
                    $data = json_decode(json_encode($data), true);
                // }
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Data must be of type array or object, %s given',
                        gettype($data)
                    )
                );
            }
        }

        // if($flatten) {
        //     $data = self::flatten($data, $this->dot);
        // }

        $this->tokeniser->setTokens($data);

        return $this;
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
     * Returns a formatted string.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return (string) $this->formatter;
        } catch(\Exception $e) {
            $this->logger->warning(
                '{0} -- {1}', array(__CLASS__, $e->getMessage(),
                )
            );
        }
    }

    // public function setFormatter(Format $formatter)
    // {
    //     $this->formatter = $formatter;
    // }

    // public function getTokens()
    // {
    //     return $this->tokeniser->getTokens();
    // }

    // public function setHelper(Helper $helper)
    // {
    //     $this->helper = $helper;
    // }


    // public function getFormatter()
    // {
    //     return $this->formatter;
    // }

}