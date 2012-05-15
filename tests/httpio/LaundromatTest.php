<?php
/**
 * This file is part of the httpio package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package httpio
 * @subpackage Tests
 */
namespace itbz\httpio;
use PHPUnit_Framework_TestCase;


/**
 * Test the Laundromat class
 * @package httpio
 * @subpackage Tests
 */
class LaundromatTest extends PHPUnit_Framework_TestCase
{

    function testIsReset()
    {
        $l = new Laundromat(array(
            'bar' => 'bar'
        ));

        // 'foo' is not a valid key
        $this->assertFalse($l->is('foo'));

        // 'bar' is a valid key, case insensitive
        $this->assertTrue($l->is('bar'));
        $this->assertTrue($l->is('BAR'));

        // remove 'bar'
        $l->remove('bAr');
        $this->assertFalse($l->is('bar'));
    }


    /**
     * @expectedException itbz\httpio\Exceptions\DataNotSetException
     */
    function testDataNotSetException()
    {
        $l = new Laundromat(array());
        $l->get('foo', 'filter...');
    }


    /**
     * @expectedException itbz\httpio\Exceptions\DataNotValidException
     */
    function testDataNotValidException()
    {
        $l = new Laundromat(array(
            'foo' => 123
        ));
        $l->get('foo', '/abc/');
    }


    function testGet()
    {
        $l = new Laundromat(array(
            'bar' => 'bar',
            'foo' => 'yo/yo'
        ));

        // Validate using callback function
        $data = $l->get('bar', 'ctype_alpha');
        $this->assertSame('bar', $data);

        // Validate using filter_var filter
        $data = $l->get('bar', FILTER_SANITIZE_STRING);
        $this->assertSame('bar', $data);
 
        // Validate using regular expression
        $data = $l->get('foo', '/^yo\/yo$/');
        $this->assertSame('yo/yo', $data);
    }

}