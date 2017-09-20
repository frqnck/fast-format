<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

use Apix\String\Helper\UpCaseHelper as UnderTest;
use Apix\String\Helper\FilterRegistry;

class UpCaseHelperTest extends AbstractTestCase
{

    protected function setUp()
    {
        $this->helper = new UnderTest(false);
    }

    public function providerParse()
    {
        return array(
            'default' => array('name', 'frQnCk', 'frQnCk'),
            'upper'   => array('NAME', 'franck', 'FRANCK'),
            'ucfirst' => array('Name', 'franck', 'Franck'),
            'integer' => array('Var', 123, 123),
            'dotted'  => array('Some.name', 'some value', 'Some value'),
            'titlecased 1' => array('NamE', 'foo bar', 'Foo Bar'),
            'titlecased 2'  => array('Some.namE', 'some value', 'Some Value'),
        );
    }

    public function providerParseMultiByte()
    {
        return array(
            'default' => array('name', 'αάβξς', 'αάβξς'),
            'upper'   => array('NAME', 'αάβξς', 'ΑΆΒΞΣ'),
            'ucfirst' => array('Name', 'αβ', 'Αβ'),
            'integer' => array('Var', 123, 123),
            'dotted'  => array('Some.name', 'αάβξς αάβξς', 'Αάβξς αάβξς'),
            'titlecased 1' => array('NamE', 'αάβξς αάβξς', 'Αάβξς Αάβξς'),
            'titlecased 2'  => array('Some.namE', 'αάβξς αάβξς', 'Αάβξς Αάβξς')
        );
    }

    /**
     * @dataProvider providerParseMultiByte
     */
    public function testParseMultiByte($token, $val, $expected)
    {
        $this->helper = new UnderTest(true);
        $this->assertSame($expected, $this->helper->parse($token, $val));
    }

    public function testParseDefaultToLower()
    {
        $val = 'frQnCk';
        $this->helper = new UnderTest(true, true);
        $this->assertSame('frqnck', $this->helper->parseMultiByte('x', $val));
        $this->assertSame('frqnck', $this->helper->parseSingleByte('x', $val));
    }

    public function testSetCharset()
    {
        $this->assertFalse($this->helper->setCharset('qwertyuiop'));
        $this->assertSame(array('UTF-16'), $this->helper->setCharset('utf-16'));
        $this->assertSame(
            array('ASCII', 'UTF-8'),
            $this->helper->setCharset('auto')
        );
    }

    public function testGetSetFilterRegistry()
    {
        $this->assertNull($this->helper->getFilterRegistry());
        $this->helper->setFilterRegistry(new FilterRegistry);
        $this->assertInstanceOf(
            __NAMESPACE__ . '\FilterRegistry',
            $this->helper->getFilterRegistry()
        );
    }

}