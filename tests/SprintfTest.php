<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String;

use Apix\String\Sprintf as UnderTest;

class SprintfTest extends BaseTestCase
{
    protected $tokens = array(
        'foo'  => 'bar',
        'name' => 'franck',
        'last' => 'cassedanne',
        'verb' => 'jumped',
        'subj' => array('det' => 'The', 'np' => 'cat'),
        'w' => array('where' => array('number' => 1, 'np' => 'table')),
        'some_digits' => 123,
        
        'bad' => 'good',
        'some' => array('silly' => 100)
    );

    protected function setUp()
    {
        $this->formatter = new UnderTest;
    }

    public function providerToString()
    {
        return array(
            'benchmark' => array(
                'Oh! {subj.det%s} {subj.np%s} {verb} onto {w.where.number%s} ({w.where.number%e}) {w.where.np}',
                array(
                    'Oh! %s %s jumped onto %s (%e) table', 'The', 'cat', 1, 1
                )
            ),
            'example' => array(
                "A {bad} example has {some.silly%01.4f} monkeys",
                array(
                     "A %s example has %01.4f monkeys",
                     $this->tokens['bad'], $this->tokens['some']['silly']
                )
            ),
            'simple' => array(
                '{name} {last}',
                array(
                    '%1$s %2$s', $this->tokens['name'], $this->tokens['last']
                )
            ),
            'complex' => array(
                "{w.where.np%' 9s}: {some_digits%'09s}",
                array(
                    "%' 9s: %'09d",
                    $this->tokens['w']['where']['np'], $this->tokens['some_digits']
                )
            ),
            'Specifying padding character' => array(
                "Padded {some_digits%'.9d}",
                array("Padded %'.9d", $this->tokens['some_digits'])
            ),
        );
    }

    /**
     * @dataProvider providerToString
     */
    public function testToString($format, $expected, $seps = self::SEPs)
    {
        $expected = call_user_func_array('sprintf', $expected);

        parent::testToString($format, $expected, $seps);
    }
}