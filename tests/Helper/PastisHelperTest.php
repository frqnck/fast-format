<?php

/**
 * This file is part of the Apix Project.z
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

use Apix\String\Helper\PastisHelper as UnderTest;

class PastisHelperTest extends AbstractTestCase
{

    protected function setUp()
    {
        $this->helper = new UnderTest;
    }

    public function providerToString()
    {
        return array(
            'benchmark' => array(
                'We are {wild.foo|upper} in {wild.bar|ucwords}, {wild.and.some.stuff|ucfirst} of us {wild.and.some.more} {stars}.',
                'We1 are ALL in The Gutter, But some of us are looking at the {stars}.'
            )
        );
    }

    public function testDefaultFilter()
    {
        $h = $this->helper;
        $this->assertSame('foo', $h('default("default_val")', 'foo'));
        $this->assertSame('default_val', $h('default("default_val")', null));
    }

    public function providerParse()
    {
        // manual tests (edge case and such). 
        $arr = array(
            '{varname}' => array('varname', 'value', 'value'),
            'default' => array('|default', null, ''),
            'default2' => array('var|default("foo")', '', 'foo'),

            'truncate' => array('Right|truncate(3)', null, 'Rig...'),
            'truncate+middle' => array('Middle|truncate(4, "middle", "+")', null, 'Mi+le'),

            'substr' => array('foobar|substr(3)', null, 'bar'),
            'substr 1' => array('foobar|substr(3, 2)', null, 'ba'),

            //
            // Test common aliases
            //
            'upper 1' => array('var|upper', 'franck', 'FRANCK'),
            'upper 2' => array('franck|upper', null, 'FRANCK'),
            'lower' => array('FOO|lower', null, 'foo'),
            'escape' => array('var|escape', "O'Reilly", "O\'Reilly"),
            // 'e' => array('%^&|e("html")', null, '%^&amp;'),
            'length' => array('foo|length', null, 3),

            //
            // Test internal php function!
            //
            'ucfirst' => array('var|ucfirst', 'franck', 'Franck'),
            'ucwords 1' => array('var|ucwords', 'foo bar', 'Foo Bar'),
            'ucwords 2' => array('var|ucwords("|")', 'foo|bar', 'Foo|Bar'),
            'ucwords 3' => array('foo,bar|ucwords(",")', null, 'Foo,Bar'),

            //
            // Test recursivly
            //
            'ltrim' => array('-bar-|ltrim("-")', null, 'bar-'),
            'rtrim' => array('-bar-|rtrim("-")', null, '-bar'),
            'ltrim+rtrim' => array('-bar-|ltrim("-")|rtrim("-")', null, 'bar'),
            
            'brackets 1' => array(' !bar! |trim|trim("!")', null, 'bar'),
            'brackets 2' => array('franck|upper|strrev', null, 'KCNARF'),

            'array_sum' => array('var|array_sum', array(1, 2), 3),
            'first' => array('var|first("q")', array('foo', 'bar'), 'foo'),
            'last' => array('var|last("q")', array('foo', 'bar'), 'bar'),

            'implode' => array('var|implode', array('foo','bar'), 'foobar'),
            'join' => array('var|join', array('foo', 'bar'), 'foo, bar'),
            'join 2' => array('var|join("-")', 'foo', 'f-o-o'),
            'replace' => array('var|replace("-", "_")', 'f-o-o', 'f_o_o'),
        );

        // manual math tests (edge case and such).
        $arr += array(
            'integer (as is)' => array('var', 123, 123),
            'abs' => array('var|abs', -4.2, 4.2),
            'cos' => array('var|cos', M_PI, -1.0),
            'acos' => array('var|acos', cos(M_PI), M_PI),
            'sin' => array('var|sin', 90*M_PI/180, 1.0),            
            'pi' => array('var|pi', null, M_PI), // pi(void)

            'min' => array('var|min', array(0.5,1,2,3), 0.5),
            'max' => array('var|max', array(0.5,1,2,3), 3),
            'min()' => array('var|min()', array(0.5,1,2,3), 0.5),
            'max()' => array('var|max()', array(0.5,1,2,3), 3),
            'fmod' => array('5.7|fmod(1.3)', null, 0.5)
        );

        // get the default filters        
        $filters = (new UnderTest)->getFilterRegistry()->getFilters();

        // automatically test all the declared filters. 
        // skipping any already defined tests above. 
        $arr += array_map(
            function ($k, $v) use ($arr) {
            
                if(isset($arr[$k]) || isset($arr[$v]) || strpos($v, '\\') ) {
                    return array(false, false, false);
                }

                return array('var|' . $v, 1, $v(1));
            },
            array_keys($filters),
            $filters
        );

        return $arr;
    }

}