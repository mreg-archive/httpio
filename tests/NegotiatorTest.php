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
 * Test the Negotiator class
 * @package httpio
 * @subpackage Tests
 */
class NegotiatorTest extends PHPUnit_Framework_TestCase
{

    function testParseRawAccept()
    {
        $arr = Negotiator::parseRawAccept("application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png");

        $expected = array(
            'application/xml' => 1.0,
            'application/xhtml+xml' => 1.0,
            'text/html' => 0.9,
            'text/plain' => 0.8,
            'image/png' => 1.0,
        );
        
        $this->assertSame($expected, $arr);
    }


    function testMergeRegion()
    {
        $arr = Negotiator::parseRawAccept("en-US,en;q=0.9,sv");
        $arr = Negotiator::mergeRegion($arr);
        $expected = array(
            'en' => 1.0,
            'sv' => 1.0
        );
        $this->assertSame($expected, $arr);
    }


    function testNegotiateArray()
    {
        $n = new Negotiator(array(
            'sv'=>1.0,
            'en'=>1.0
        ));
        
        $user = array(
            'en' => 0.9,
            'sv' => 1.0
        );
        
        $this->assertSame('sv', $n->negotiateArray($user));
        $this->assertEquals($user, $n->getResult());
    }

    
    function testNegotiate()
    {
        $n = new Negotiator(array(
            'text/html'=>1.0,
            'application/xhtml+xml'=>1.0
        ));
        
        $result = $n->negotiate("application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png");
        
        $this->assertSame('application/xhtml+xml', $result);
    }


    function testNegotiateNoMatch()
    {
        // text/html is the default
        $n = new Negotiator(array(
            'text/html'=>1.0,
            'application/xhtml+xml'=>1.0
        ));
        $this->assertSame('text/html', $n->negotiate(""));
    }

}
