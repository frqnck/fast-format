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
 * The Filter Registry.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class FilterRegistry
{
    /**
     * Holds the registered filters.
     * @var array
     */
    protected $filters = array();

    /**
     * Holds the registered filter objects.
     * @var array|null
     */
    protected $objects;

    /**
     * Constructor.
     *
     * @param array $map A map of helpers.
     */
    public function __construct(
        array $filters = array(), array $objects = array()
    ) {
        $this->filters = $filters;
        $this->objects = $objects;
    }

    /**
     * Returns all the registered filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Sets/injects filters or object and methods.
     *
     * @param  string|class  $mixed
     * @param  callable|null
     * @return self
     */
    public function inject($mixed, $cb = null)
    {
        if (is_object($mixed)) {
            $name = get_class($mixed);
            $this->objects[$name] = $mixed;
            foreach(get_class_methods($mixed) as $method) {
                $this->filters[$method] = $name;
            }
        } elseif ((array) $mixed === $mixed) {
            $this->filters += $mixed;
        } elseif($cb) {
            $this->filters[$mixed] = $cb;
        } else {
            $this->filters[] = $mixed;
        }

        return $this;
    }

    // public function injectPrefix($ns, $mixed, $cb = null)
    // {
    //     if (is_object($mixed)) {
    //         $name = get_class($mixed);
    //         $this->objects[$name] = $mixed;
    //         foreach(get_class_methods($mixed) as $method) {
    //             $this->filters[$ns . $method] = $name;
    //         }
    //     } elseif ((array) $mixed === $mixed) {
    //         $this->filters += $mixed;
    //     } elseif($cb) {
    //         $this->filters[$ns . $mixed] = $cb;
    //     } else {
    //         $this->filters[$ns . $mixed] = $mixed;
    //     }

    //     return $this;
    // }

    /**
     * Excludes the named filter(s).
     *
     * @param  string|array
     * @return self
     */
    public function exclude($names)
    {
        foreach((array) $names as $name) {
            if(isset($this->filters[$name])
                || !$name = array_search($name, $this->filters)
            ) {
                unset($this->filters[$name]);
            }
        }

        return $this;
    }

    public function has($name)
    {
        return isset($this->filters[$name]) || in_array($name, $this->filters);
    }

    /**
     * Returns the named filter.
     *
     * @param  string $name The function or method name to reach.
     * @return callable|array[object, method]|string
     */
    public function get($name)
    {
        // -> key: can be alias, object pointer or lambda func.
        if(isset($this->filters[$name])) {
            $cb = $this->filters[$name];
        }

        // -> val: is it a named function.
        elseif(in_array($name, $this->filters) ) {
            $cb = $name;
        }

        // -> does not exist.
        else {
            $msg = sprintf('Filter "%s()" is not defined', $name);
            throw new \BadFunctionCallException($msg);

            return $name;
        }

        return !is_callable($cb) && isset($this->objects[$cb])
               ? array(&$this->objects[$cb], $name)
               : $cb;
    }

    /**
     * Returns the rendered filter.
     *
     * @param  string                         $filter_name
     * @param  callable|array[object, method] $mixed
     * @param  array                          $args
     * @return mixed The result of the filter.
     * @throws \InvalidArgumentException
     */
    public function __invoke($filter_name, $mixed, array $args)
    {
        try {
            return call_user_func_array($mixed, $args);
        } catch(\Exception $e) {

            // TODO: parse the errors accordingly:
            $msg = $e->getMessage();
            // : 'is missing argument ' . count($args)+1;

            throw new \InvalidArgumentException(
                sprintf('Filter "%s" - %s', $filter_name, $msg)
            );
        }
    }

    /**
     * Invokes the named injected filter.
     *
     * @param string $name The filter name.
     * @see   self::invoke
     */
    public function invokeInjectedFilter($name)
    {
        $args = func_get_args();
        array_shift($args);

        return self::__invoke($name, $this->get($name), $args);
    }

}