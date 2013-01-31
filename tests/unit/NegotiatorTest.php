<?php
namespace iio\httpio;

class NegotiatorTest extends \PHPUnit_Framework_TestCase
{
    public function testParseRawAccept()
    {
        $accept = "application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png";
        $arr = Negotiator::parseRawAccept($accept);

        $expected = array(
            'application/xml' => 1.0,
            'application/xhtml+xml' => 1.0,
            'text/html' => 0.9,
            'text/plain' => 0.8,
            'image/png' => 1.0,
        );

        $this->assertSame($expected, $arr);
    }

    public function testMergeRegion()
    {
        $arr = Negotiator::parseRawAccept("en-US,en;q=0.9,sv");
        $arr = Negotiator::mergeRegion($arr);
        $expected = array(
            'en' => 1.0,
            'sv' => 1.0
        );
        $this->assertSame($expected, $arr);
    }

    public function testNegotiateArray()
    {
        $n = new Negotiator(
            array(
                'sv'=>1.0,
                'en'=>1.0
            )
        );

        $user = array(
            'en' => 0.9,
            'sv' => 1.0
        );

        $this->assertSame('sv', $n->negotiateArray($user));
        $this->assertEquals($user, $n->getResult());
    }

    public function testNegotiate()
    {
        $n = new Negotiator(
            array(
                'text/html'=>1.0,
                'application/xhtml+xml'=>1.0
            )
        );

        $result = $n->negotiate("application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png");

        $this->assertSame('application/xhtml+xml', $result);
    }

    public function testNegotiateNoMatch()
    {
        // text/html is the default
        $n = new Negotiator(
            array(
                'text/html'=>1.0,
                'application/xhtml+xml'=>1.0
            )
        );
        $this->assertSame('text/html', $n->negotiate(""));
    }
}
