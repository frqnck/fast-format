<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Filter;

use Apix\String\Filter\StringFilter as UnderTest;
use Apix\String\Fixtures\FooClass;

class StringFilterTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $this->filter = new UnderTest;
    }

    public function testSetCharset()
    {
        $this->assertFalse($this->filter->setCharset('qwertyuiop'));
        $this->assertSame(array('UTF-16'), $this->filter->setCharset('utf-16'));
        $this->assertSame(
            array('ASCII', 'UTF-8'),
            $this->filter->setCharset('auto')
        );
    }

    public function providerTruncate()
    {
        return array(
            array(array('foobar', 6, 'right', '!'), 'foobar'),
            array(array('foobar', 5, 'right', '!'), 'fooba!'),
            array(array('foobar', 5, 'midle', '!'), 'foo!ar'),
            array(array('foobar', 2, 'middle', '!'), 'f!r')
        );
    }

    /**
     * @dataProvider providerTruncate
     */
    public function testTruncate($args, $exp)
    {
        list($str, $width, $type, $trail) = $args;
        $val = $this->filter->truncate($str, $width, $type, $trail);
        $this->assertSame($exp, $val);
    }

    public function providerReplace()
    {
        $iter = new \ArrayIterator; 
        $iter['foo'] = 'ba';
        return array(
            array(array('foobar', 'o', '00'), 'f00bar'),
            array(array('foobar', array('o'=>'O', 'f'=>'B'), null), 'BOObar'),
            array(array('foobar', $iter, null), 'babar'),
        );
    }

    /**
     * @dataProvider providerReplace
     */
    public function testReplace($args, $exp)
    {
        list($str, $from, $to) = $args;
        $val = $this->filter->replace($str, $from, $to);
        $this->assertSame($exp, $val);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReplaceThrowsRuntimeException()
    {
        $this->filter->replace('str', 'foo');
    }

    public function providerSlugify()
    {
        return array(
            array('foo bar', 'foo-bar'),
            array('École du Savoir', 'ecole-du-savoir'),
            array('Foo&bar-_-$a£n#d¢B&ar!?', 'foo_bar_a_n_d_b_ar', '_')
        );
    }

    /**
     * @dataProvider providerSlugify
     */
    public function testSlugify($str, $exp, $slug = '-')
    {
        $val = $this->filter->slugify($str, $slug);
        $this->assertSame($exp, $val);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSlugifyThrowsRuntimeException()
    {
        $this->filter->slugify('');
    }

    // -- TODO -- //

    public function providerTransliterate()
    {
        return array(
            array('キャンパス', 'kyanpasu'),
            array('Αλφαβητικός Κατάλογος', 'Alphabētikós Katálogos'),
            array('биологическом', 'biologichyeskom'),
        );
    }
    
    /**
     * @dataProvider providerTransliterate
     */
    public function OFF_testTransliterate($str, $exp, $from = null, $to = 'us-ascii')
    {
        $from = mb_detect_encoding($str);
        // $val = $this->filter->transliterate($str, $from, $to);
        $val = iconv($from, 'ASCII//TRANSLIT', utf8_encode($str));
        $this->assertSame($exp, $val, "Err with: " . $str);
    }

}