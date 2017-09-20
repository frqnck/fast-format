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
 * Acts as a filter parser.
 *
 * The default filter delimiters can be override by textending this class.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class FilterParser extends AbstractHelper
{
    /**
     * Holds the filter separator (pipe).
     * @var string
     */
    const PIPE = '|';

    /**
     * Holds the starting enclosure character (one character only for now).
     * @var string
     */
    const OPEN = '(';

    /**
     * Holds the starting enclosure character (one character only for now).
     * @var string
     */
    const CLOSE = ')';

    /**
     * Holds the filter arguments separator/delimiter.
     * @var string
     */
    const COMMA = ',';

    /**
     * Holds the regular expression to split chained filters upon.
     * @var string
     */
    protected $pipe_regex;

    /**
     * Holds the regular expression to extract filters parameters upon.
     * @var string
     */
    protected $comma_regex;

    /**
     * Constructor.
     *
     * @param FilterRegistry $filter_registry
     */
    public function __construct(FilterRegistry $filter_registry)
    {
        $this->filter_registry = $filter_registry;

        $this->pipe_regex = '/' . preg_quote(static::PIPE)
                            . '(?![^' . preg_quote(static::OPEN) . ')]*'
                            . preg_quote(static::CLOSE) . ')/';

        $this->comma_regex = '/\'[^\']++\'|"[^")]++"|\[[^\])]++\]'
                             . '|[^' . preg_quote(static::COMMA) . '\s?]++/u';
    }

    /**
     * {@inheritdoc}
     */
    public function parse($token, $val)
    {
        if(false !== ($pos = strpos($token, static::PIPE))) {
            if(null === $val) {
                $_token = substr($token, 0, $pos);
                if($this->tokeniser) {
                    $val = $this->tokeniser->getToken($_token, true);
                }
                $val = null !== $val ? $val : $_token;
            }

            // recursively apply the chained filters
            $filters = preg_split(
                $this->pipe_regex, $token, null, PREG_SPLIT_NO_EMPTY
            );

            $val = $this->applyFilters($filters, $val);
        }

        return $val;
    }

    /**
     * Applies the provided filters to the given string.
     *
     * @param array  $filters
     * @param string $val
     * return string
     */
    protected function applyFilters(array $filters, $val)
    {
        $i = 0;
        while(isset($filters[++$i])) {
            $val = $this->__invoke($filters[$i], $val);
        }

        return $val;
    }

    /**
     * Returns the rendered filter.
     *
     * @param  string $name
     * @param  string $val
     * @return mixed
     * @see    FilterRegistry::__invoke
     */
    public function __invoke($name, $val)
    {
        // Beware: $name is set by reference.
        $enclosed = self::getEnclosed($name);

        if($cb = $this->filter_registry->get($name)) {

            $defaults = self::getParameterDefaults($name, $cb);

            $params = $this->extrapolatesEnclosed($enclosed);

            array_unshift($params, $val);

            // Merge params
            $params = self::mergeParameters($params, $defaults);

            return $this->filter_registry->__invoke($name, $cb, $params);
        }
    } // @codeCoverageIgnore

    /**
     * Extracts/splits the parameters from the given name (by reference).
     *
     * @param  string &$name
     * @return string
     */
    static public function getEnclosed( &$name )
    {
        if(false !== $pos = strpos($name, static::OPEN)) {
            $enclosed = substr($name, $pos+1, -1);
            $name = substr($name, 0, $pos);

            return $enclosed;
        }
    }

    /**
     * Extracts/splits the parameters from the given name (by reference).
     *
     * @param  string &$name
     * @return array
     */
    public function extrapolatesEnclosed($enclosed)
    {
        $params = array();
        if(null !== $enclosed ) {
            // extract the parameters
            $params = $this->extractParameters($enclosed);

            // trim enclosing spaces, and quotes
            array_walk(
                $params, function (&$v, $k) {
                    if(isset($v[0])) {
                        $v = self::trimQuotes(trim($v));
                    }
                }
            );
        }

        return $params;
    }

    public function extractParameters($str)
    {
        // return str_getcsv($enclosed, static::COMMA);
        preg_match_all($this->comma_regex, $str, $params);

        return $params[0];
    }

    static private function trimQuotes($v)
    {
        return trim($v, $v[0]==='"' ? '"': '\'');
    }

    /**
     * Returns the merged parameters adding the default values and types.
     *
     * @param  array $params
     * @param  array $defaults
     * @return array
     */
    static public function mergeParameters(array $params, array $defaults)
    {
        $args = array();
        $i = 0;
        foreach($defaults as $name => $default) {

            $val = !isset($params[$i])
                   || $default['type'] == 'integer'
                   && !ctype_digit($params[$i])
                    ? $default['value']
                    : $params[$i];

            if(null !== $val) {

                if($default['type'] !== 'NULL' ) {
                    self::setType($val, $default['type']);
                }

                $args[$name] = $val;
            }
            ++$i;
        }

        // Add the remaining params...
        $args += array_slice($params, $i);

        return $args;
    }

    /**
     * Gets the default values and types.
     *
     * @param  string         $name The name is used as a cache id.
     * @param  Callable|array $cb   Either a valid Callable or an array containing a class/method to reflect upon.
     *                             containing a class/method to reflect upon.
     * @return array[name[value, type]
     */
    static public function getParameterDefaults($name, $cb)
    {
        static $defaults = array();

        if(!isset($defaults[$name])) {

            switch(true) {
            case (array) $cb === $cb:
                $r = new \ReflectionMethod($cb[0], $cb[1]);
                break;

            case (object) $cb !== $cb && strpos($cb, '::'):
                $r = new \ReflectionMethod($cb);
                break;

            default:
                $r = new \ReflectionFunction($cb);
            }

            $defaults[$name] = array();
            foreach($r->getParameters() as $p) {
                $default = $p->isDefaultValueAvailable()
                            ? $p->getDefaultValue()
                            : null;

                $defaults[$name][$p->getName()] = array(
                    'value' => $default,
                    'type'  => self::getType($p, $default),
                );
            }
        }

        return $defaults[$name];
    }

    /**
     * Casts the string value to the given type.
     *
     * @param  string &$value
     * @param  string $type
     * @return void
     */
    static protected function setType(&$value, $type)
    {
        if($type === 'boolean') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        } elseif($type === 'array' && is_string($value)) {
            $value = json_decode($value);

        } else {
            settype($value, $type);
        }
    }

    /**
     * Gets a parameter's type (no-in-use-yet).
     *
     * @param  \ReflectionParameter $param
     * @param  mixed                $value
     * @return string|null
     */
    static protected function getType(\ReflectionParameter $param, $value)
    {
        // use this from >= PHP7.
        // if(method_exists($param, 'hasType') ) {
        //     return $param->hasType()
        //             ? $param->getType()
        //             : 'NULL';

        // use the followings as fallback when < PHP7.
        // } else

        if ($param->isArray()) {
            return 'array';

        } else {
            return gettype($value);
        }
    }

}