<?php

namespace SjorsO\Sup\Tests;

use Exception;
use SjorsO\Sup\Streams\BitStream;

class BitStreamTest extends BaseTestCase
{
    /** @test */
    function it_reads_single_bits()
    {
        // 0b00001011 + 0b11111111
        $stream = Bitstream::fromData("\xb\xff");

        $this->assertSame(0, $stream->bit());
        $this->assertSame(0, $stream->bit());
        $this->assertSame(0, $stream->bit());
        $this->assertSame(0, $stream->bit());

        $this->assertSame(1, $stream->bit());
        $this->assertSame(0, $stream->bit());
        $this->assertSame(1, $stream->bit());
        $this->assertSame(1, $stream->bit());

        $this->assertSame(1, $stream->bit());
        $this->assertSame(1, $stream->bit());
        $this->assertSame(1, $stream->bit());
        $this->assertSame(1, $stream->bit());

        $this->assertSame(1, $stream->bit());
        $this->assertSame(1, $stream->bit());
        $this->assertSame(1, $stream->bit());
        $this->assertSame(1, $stream->bit());
    }

    /** @test */
    function it_reads_bits_as_booleans()
    {
        // 0b10000000
        $stream = Bitstream::fromData("\x80");

        $this->assertSame(true, $stream->bool());
        $this->assertSame(false, $stream->bool());
        $this->assertSame(false, $stream->bool());
    }

    /** @test */
    function it_reads_multiple_bits()
    {
        // 0b00001011 + 0b11111111
        $stream = Bitstream::fromData("\xb\xff");

        // 0b0000 = 0
        $this->assertSame(0, $stream->bits(4));

        // 0b10 = 2
        $this->assertSame(2, $stream->bits(2));

        // 0b11111111 = 255
        $this->assertSame(255, $stream->bits(8));

        // 0b11 = 3
        $this->assertSame(3, $stream->bits(2));
    }

    /** @test */
    function reading_more_than_the_data_length_throws_an_exception()
    {
        $this->expectException(Exception::class);

        $stream = Bitstream::fromData("\xb");

        $this->assertSame(0, $stream->bit());
        $this->assertSame(0, $stream->bit());
        $this->assertSame(0, $stream->bit());
        $this->assertSame(0, $stream->bit());

        $this->assertSame(1, $stream->bit());
        $this->assertSame(0, $stream->bit());
        $this->assertSame(1, $stream->bit());
        $this->assertSame(1, $stream->bit());

        $this->assertSame(1, $stream->bit());
    }
}
