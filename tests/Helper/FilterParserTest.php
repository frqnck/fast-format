<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

use Apix\String\Helper\FilterParser as UnderTest;
use Apix\String\Fixtures\FooClass;
use Apix\String\Template;

class FilterParserTest extends AbstractTestCase
{

    protected $dumper;
    protected $dumper_expected = array('varname', 'default');

    protected function setUp()
    {
        $this->filters = $this->getFilterRegistry();

        $this->helper = new UnderTest($this->filters);
    }

    protected function _helperArgsToArray(array $a)
    {
        $arr = array();
        foreach($a as $k => $v) {
            $arr[$k] = array(
                'value' => $v,
                'type' => gettype($v),
                // 'type2' => gettype($v), // temp
            );
        }

        return $arr;
    }

    protected function getFilterRegistry()
    {
        $registry = new FilterRegistry;

        // default filters
        $this->dumper = function ($required, $optional = 'default') {
            return func_get_args();
        };

        $registry
            ->inject(new FooClass)
            ->inject(
                'staticFilter', 'Apix\String\Fixtures\FooClass::staticFilter'
            )
            ->inject('dumper', $this->dumper)
            ->inject(
                'func_void', function () {
                    // takes no args and returns void...
                }
            )
            ->inject(
                'quoter', function ($val, $quote = '"') {
                    return $quote . $val . $quote;
                }
            )
            ->inject(
                'func_mix', function ($b = true, $i = 9, $n = null, $f = 1.0) {

                }
            )
            ->inject(
                'unCamel', function ($camel, $glue = ' ') {
                    return preg_replace(
                        '/([a-z0-9])([A-Z])/', "$1$glue$2", $camel
                    );
                }
            )
            ->inject(
                'ucwords', function ($str, $glue = ' ') {
                    return ucwords($str, $glue);
                }
            )
            ->inject(
                'getOptions', function (array $opts = array()) {
                    return func_get_args();
                }
            )
            ->inject(
                'getArgs', function ($req1, $req2, $opt = null) {
                    return func_get_args();
                }
            );

            return $registry;
    }

    public function providergetParameterDefaultsAndTypeCasts()
    {
        return array(
            array(
                // null will casts to string (might need to review this)?
                'instanceFilter', array('value' => null),
                'instanceFilter(true)', array('value' => 'true'),
            ),
            array(
                'FooClass::staticFilter', array('value' => 'string'),
                'staticFilter(1024)', array('value' => (string) 1024),
            ),
            array(
                'dumper', array('required' => null, 'optional' => 'default'),
                null, array('optional' => 'default')
            ),
            array(
                'quoter', array('val' => null, 'quote' => '"'),
                null, array('quote' => '"')
            ),
            array(
                'quoter2', array('val' => null, 'quote' => '"'),
                'quoter("*")', array('val' => '*', 'quote' => '"')
            ),
            array(
                'func_void', array(),
                'func_void("hi")', array('hi')
            ),
            array(
                'func_mix 1', array('b'=>true, 'i'=>9, 'n'=>null, 'f'=>1.0),
                'func_mix(no,0,2)', array('b'=>false,'i'=>0,'n'=>'2','f'=>1.0)
            ),
            array(
                'func_mix 1.1', array('b'=>true, 'i'=>9, 'n'=>null, 'f'=>1.0),
                'func_mix(yes,10,2)', array(
                    'b'=>true,'i'=>10,'n'=>'2','f'=>1.0
                )
            ),
            array(
                'func_mix 2', array('b'=>true, 'i'=>9, 'n'=>null, 'f'=>1.0),
                'func_mix(0,null,"",0)', array(
                    'b'=>false,'i'=>9,'n'=>'','f'=>0.0
                )
            ),
            array(
                'func_mix 3', array('b'=>true,'i'=>9,'n'=>null,'f'=>1.0),
                'func_mix(1,null)', array('b'=>true,'i'=>9,'f'=>1.0)
            ),
            array(
                'func_mix 3.1', array('b'=>true, 'i'=>9, 'n'=>null, 'f'=>1.0),
                'func_mix(1,NULL,0,\'9\')', array(
                    'b'=>true,'i'=>9,'n'=>'0','f'=>9.0
                )
            ),
            'Additional params should be appended sequentially' => array(
                'dumper', array('required' => null, 'optional' => 'default'),
                'dumper("a", "b", "c")', array(
                    'required' => 'a',
                    'optional' => 'b',
                    0 => 'c',
                )
            ),
            'Should handle "[...]" as array if type-casted as such' => array(
                'getOptions', array( 'opts' => array() ),
                'getOptions(["a","b","c"],["z"])', array(
                    'opts' => array("a", "b", "c"),
                    0 => '["z"]' // should be a string (the default type-cast)
                )
            )
        );
    }

    /**
     * @group ut
     * @dataProvider providergetParameterDefaultsAndTypeCasts
     */
    public function testGetParameterDefaultsAndTypeCasts(
        $name, array $exp_default, $str, array $exp_actual
    ) {
        $name = $str ?: $name;

        $enclosed_str = $this->helper->getEnclosed($name);
        $cb = $this->filters->get($name);
        $defaults = $this->helper->getParameterDefaults($name, $cb);
        $args = $this->helper->extrapolatesEnclosed($enclosed_str);

        $this->assertSame($this->_helperArgsToArray($exp_default), $defaults);

        $merged = $this->helper->mergeParameters($args, $defaults);
        $this->assertSame($exp_actual, $merged);
    }

    public function testWithArrayStuff()
    {
        $name = 'getOptions(["a","b","c"])';

        $enclosed_str = $this->helper->getEnclosed($name);
        $cb = $this->filters->get($name);
        $defaults = $this->helper->getParameterDefaults($name, $cb);
        $args = $this->helper->extrapolatesEnclosed($enclosed_str);

        $merged = $this->helper->mergeParameters($args, $defaults);

        $this->assertSame(array("a", "b", "c"), $merged['opts']);
    }

    // protected static function getMethod($name, $class)
    // {
    //   $class = new \ReflectionClass(($class));
    //   $method = $class->getMethod($name);
    //   $method->setAccessible(true);

    //   return $method;
    // }

    public function testApplyWithAnInstanceFilter()
    {
        $this->filters->inject(new FooClass);
        $this->assertSame(
            'foo', $this->helper->__invoke('instanceFilter', 'foo')
        );
    }

    public function testApplyWithAStaticFilter()
    {
        $this->filters->inject(new FooClass);
        $this->assertSame('foo', $this->helper->__invoke('staticFilter', 'foo'));
    }

    public function testApplyWithMixedTypes()
    {
        $this->filters
            ->inject(new FooClass)
            ->inject('dumper', $this->dumper);

        $this->assertSame(
            $this->dumper_expected,
            $this->helper->__invoke('dumper', 'varname')
        );

        $this->assertSame(
            'foo',
            $this->helper->__invoke('staticFilter', 'foo')
        );

    }

    public function testParseUseGetsTokensFromTemplate()
    {
        $template = new Template($this->helper);
        // $this->assertSame($this->helper, $template->getHelper());

        $this->assertNull($this->helper->parse('varname|func_void', null));

        $this->assertSame(
            $this->dumper_expected,
            $this->helper->parse('varname|dumper', null)
        );
    }

    public function providerParse()
    {
        $arr = array(
            'default' => array('name', 'frQnck', 'frQnck'),

            //
            // Test injected helpers
            //
            'quoter1' => array('quote|quoter', 'frQnck', '"frQnck"'),
            'quoter2' => array('varname|quoter("%")', 'frQnck', '%frQnck%'),
            'quoter3' => array('varname|quoter(\'%\')', 'frQnck', '%frQnck%'),
            'quoter4' => array('varname|quoter(\'"\')', 'frQnck', '"frQnck"'),

            // inconsistency due to: str_getcsv(), shoudl be "'frQnck'" ??
            'quoter5' => array('frQnck|quoter("~")', null, "~frQnck~"),

            'dumper_1' => array('varname|dumper', null, $this->dumper_expected),
            'dumper_2' => array('varname|dumper', 'varname', $this->dumper_expected),

            'unCamel' => array('camelCase|unCamel', null, 'camel Case'),
            'unCamel + ucwords (chained)' => array(
                'var|unCamel("-")|ucwords("-")', 'camelCase', 'Camel-Case'
            ),

        );

        // get the default filters
        $filters = (new FilterRegistry)->getFilters();

        // test automatically all the declared helpers.
        $arr += array_map(
            function ($k) {
                return array('varname|' . $k, 1, $k('1'));
            },
            $filters
        );

        return $arr;
    }

}