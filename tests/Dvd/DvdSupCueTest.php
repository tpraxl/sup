<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Formats\Dvd\DvdSupCue;
use SjorsO\Sup\Streams\Stream;

class DvdSupCueTest extends BaseTestCase
{
    /** @test */
    function it_can_parse_a_cue()
    {
        $filePath = $this->testFilePath.'sup-dvd/01-section-01.dat';

        $stream = new Stream($filePath);

        $cue = new DvdSupCue($stream, $filePath);

        // 01:34:50,184 --> 01:34:56,181
        $this->assertSame(5690185, $cue->getStartTime());
        // $this->assertSame(5696181, $cue->getEndTime());

        $this->assertSame(720, $cue->getWidth(), 'Width is wrong');
        $this->assertSame(478, $cue->getHeight(), 'Height is wrong');

        $this->assertSame(0, $cue->getX());
        $this->assertSame(2, $cue->getY());

        $this->assertSame(4166, $stream->position());
    }

    /** @test */
    function it_can_extract_an_image()
    {
        $filePath = $this->testFilePath.'sup-dvd/01-section-01.dat';

        $stream = new Stream($filePath);

        $cue = new DvdSupCue($stream, $filePath);

        $filePath = $cue->extractImage($this->tempFilesDirectory);

        exit;
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
