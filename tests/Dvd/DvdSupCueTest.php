<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Formats\Dvd\DvdSupCue;
use SjorsO\Sup\Streams\Stream;

class DvdSupCueTest extends BaseTestCase
{
    private function makeDvdSupCue($filePath)
    {
        $stream = new Stream($filePath);

        return new DvdSupCue($stream, $filePath);
    }

    /** @test */
    function it_can_parse_a_cue()
    {
        $cue = $this->makeDvdSupCue($this->testFilePath.'sup-dvd/01-section-01.dat');

        // 01:34:50,184 --> 01:34:56,181
        $this->assertSame(5690185, $cue->getStartTime());
        // $this->assertSame(5696181, $cue->getEndTime());

        $this->assertSame(720, $cue->getWidth(), 'Width is wrong');
        $this->assertSame(478, $cue->getHeight(), 'Height is wrong');

        $this->assertSame(0, $cue->getX());
        $this->assertSame(2, $cue->getY());
    }

    /** @test */
    function it_can_extract_an_image()
    {
        $cue = $this->makeDvdSupCue($this->testFilePath.'sup-dvd/01-section-01.dat');

        $filePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileHashSnapshot($filePath);
    }

    /** @test */
    function it_can_extract_another_dvd_sup_image()
    {
        $this->markTestSkipped('dvd sup format does not work yet');

        $cue = $this->makeDvdSupCue($this->testFilePath.'sup-dvd/02-section-01.dat');

        $filePath = $cue->extractImage($this->tempFilesDirectory);
    }

//    /** @test */
//    function it_can_parse_another_cue()
//    {
//        $filePath = $this->testFilePath.'sup-dvd/01-section-01+02.dat';
//
//        $stream = new Stream($filePath, 4166);
//
//        $cue = new DvdSupCue($stream, $filePath);
//
//        // 01:34:50,184 --> 01:34:56,181
//        $this->assertSame(60*60*1000 + 34*60*1000 + 50185, $cue->getStartTime());
//        // $this->assertSame(60*60*1000 + 34*60*1000 + 56181, $cue->getEndTime());
//
//        $this->assertSame(719, $cue->getWidth(), 'Width is wrong');
//        $this->assertSame(477, $cue->getHeight(), 'Height is wrong');
//
//        $this->assertSame(0, $cue->getX());
//        $this->assertSame(2, $cue->getY());
//
//        $this->assertSame(4165, $stream->position());
//    }
}
