<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    protected $helper;
    
    protected function setUp()
    {
        throw new \Exception(
            sprintf(
                'Missing "%s::setUp()" method.', get_class($this)
            )
        );
        
        // $this->helper = new UnderTest;
    }

    protected function tearDown()
    {
        unset($this->helper);
    }

    /**
     * @dataProvider providerParse
     */
    public function testParse($token, $val, $expected)
    {
        $this->assertSame($expected, $this->helper->parse($token, $val));
    }

}