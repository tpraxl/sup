<?php

namespace SjorsO\Sup\Tests;

use Exception;
use SjorsO\Sup\Streams\BlurayRleStream;

class RleStreamTest extends BaseTestCase
{
    private function createResource($data)
    {
        $stream = fopen('php://memory', 'rb+');

        fwrite($stream, $data);

        rewind($stream);

        return $stream;
    }

    private function assertRleData($data, $expects)
    {
        $resource = $this->createResource($data);

        $stream = new BlurayRleStream($resource, 0, strlen($data));

        $info = $stream->readNext();

        $this->assertSame($expects, $info);
    }

    /** @test */
    function it_reads_unencoded_data()
    {
        $this->assertRleData("\x10", [
            'paletteIndex' => 16,
            'runLength'    => 1,
            'toEndOfLine'  => false,
        ]);
    }

    /** @test */
    function it_reads_encoded_data()
    {
        $this->assertRleData("\x00\x41\x94", [
            'paletteIndex' => 0,
            'runLength'    => 404,
            'toEndOfLine'  => false,
        ]);

        $this->assertRleData("\x00\x00", [
            'paletteIndex' => 0,
            'runLength'    => 0,
            'toEndOfLine'  => true,
        ]);

        $this->assertRleData("\x00\x83\x22", [
            'paletteIndex' => 34,
            'runLength'    => 3,
            'toEndOfLine'  => false,
        ]);
    }

    /** @test */
    function it_throws_an_exception_when_exceeding_data_length()
    {
        $this->expectException(Exception::class);

        $resource = $this->createResource("\x10");

        $stream = new BlurayRleStream($resource, 0, 1);

        $stream->readNext();
        $stream->readNext();
    }
}
