<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

use Apix\String\Helper\FilterRegistry as UnderTest;

use Apix\String\Fixtures\FooClass;

class FilterRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->filter_registry = new UnderTest;

        $dumper = function ($required, $optional = 'default') {
            return func_get_args();
        };

        $this->filter_registry
            ->inject(new FooClass)
            ->inject(
                'staticFilter', 'Apix\String\Fixtures\FooClass::staticFilter'
            )
            ->inject('dumper', $dumper)
            ->inject(
                'ucwords', function ($str, $glue = ' ') {
                    return ucwords($str, $glue);
                }
            );
    }

    public function testGetFilters()
    {
        $filters = $this->filter_registry->getFilters();
        $this->assertArrayHasKey('ucwords', $filters);
        $this->assertArrayHasKey('dumper', $filters);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Filter "ucwords" - Missing argument 1
     */
    public function testInvokeInjectedFilterThrowInvalidArgumentException()
    {
        $this->filter_registry->invokeInjectedFilter('ucwords');
    }

    public function testExcludeOne()
    {
        $r = $this->filter_registry->inject('rand');
        $this->assertTrue($r->has('rand'));

        $filters = $r->exclude('rand')->getFilters();
        $this->assertFalse($r->has('rand'));
    }

    public function testExcludeMany()
    {
        $r = $this->filter_registry->inject('rand');
        $filters = $r->exclude(array('ucwords','rand'))->getFilters();
        $this->assertArrayNotHasKey('ucwords', $filters);
        $this->assertFalse($r->has('rand'));
    }

    public function providerParseThrowException()
    {
        return array(
            array('null', null, 'Filter "null()" is not defined'),
            array('foo|bar', null, 'Filter "foo|bar()" is not defined')
        );
    }

    /**
     * @dataProvider providerParseThrowException
     * FilterRegistry.php:145
     */
    public function testParseThrowException($key, $val, $expected)
    {
        $this->setExpectedException('BadFunctionCallException', $expected);

        $this->filter_registry->get($key);
    }

}