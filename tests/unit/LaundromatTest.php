<?php
namespace iio\httpio;

class LaundromatTest extends \PHPUnit_Framework_TestCase
{
    public function testIsReset()
    {
        $l = new Laundromat(
            array(
                'bar' => 'bar'
            )
        );

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
     * @expectedException iio\httpio\Exception\DataNotSetException
     */
    public function testDataNotSetException()
    {
        $l = new Laundromat(array());
        $l->get('foo', 'filter...');
    }

    /**
     * @expectedException iio\httpio\Exception\DataNotValidException
     */
    public function testDataNotValidException()
    {
        $l = new Laundromat(
            array(
                'foo' => 123
            )
        );
        $l->get('foo', '/abc/');
    }

    public function testGet()
    {
        $l = new Laundromat(
            array(
                'bar' => 'bar',
                'foo' => 'yo/yo'
            )
        );

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
