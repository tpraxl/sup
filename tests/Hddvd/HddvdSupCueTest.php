<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Formats\Hddvd\HddvdSupCue;
use SjorsO\Sup\Streams\Stream;

class HddvdSupCueTest extends BaseTestCase
{
    /** @test */
    function it_can_parse_a_cue()
    {
        $filePath = $this->testFilePath.'sup-hddvd/01-section-01.dat';

        $stream = new Stream($filePath);

        $cue = new HddvdSupCue($stream, $filePath);

        // 00:00:32,565 --> 00:00:36,945
        $this->assertSame(32566, $cue->getStartTime());
        $this->assertSame(36946, $cue->getEndTime());

        $this->assertSame(123, $cue->getWidth(), 'Width is wrong');
        $this->assertSame(472, $cue->getHeight(), 'Height is wrong');

        $this->assertSame(1655, $cue->getX());
        $this->assertSame(164, $cue->getY());

        $this->assertSame(17484, $stream->position());
    }

    /** @test */
    function it_can_parse_the_second_cue()
    {
        $filePath = $this->testFilePath.'sup-hddvd/01-section-01+02.dat';

        $stream = new Stream($filePath);

        new HddvdSupCue($stream, $filePath);

        $secondCue = new HddvdSupCue($stream, $filePath);

        // 00:03:47,393 --> 00:03:49,566
        $this->assertSame(3*60*1000 + 47394, $secondCue->getStartTime());
        $this->assertSame(3*60*1000 + 49567, $secondCue->getEndTime());

        $this->assertSame(122, $secondCue->getWidth());
        $this->assertSame(437, $secondCue->getHeight());

        $this->assertSame(1651, $secondCue->getX());
        $this->assertSame(166, $secondCue->getY());

        $this->assertSame(33248, $stream->position());
    }

    /** @test */
    function slim_vertical_cue()
    {
        $filePath = $this->testFilePath.'sup-hddvd/01.sup';

        $stream = new Stream($filePath);

        new HddvdSupCue($stream, $filePath);
        new HddvdSupCue($stream, $filePath);

        $secondCue = new HddvdSupCue($stream, $filePath);

        $this->assertSame(61, $secondCue->getWidth());
        $this->assertSame(573, $secondCue->getHeight());

        $this->assertSame(1717, $secondCue->getX());
        $this->assertSame(172, $secondCue->getY());
    }

    /** @test */
    function problematic_cue()
    {
        $filePath = $this->testFilePath.'sup-hddvd/01.sup';

        $stream = new Stream($filePath);

        for ($i = 0; $i < 22; $i++) {
            new HddvdSupCue($stream, $filePath);
        }

        $this->assertSame(283148, $stream->position());

        new HddvdSupCue($stream, $filePath);

        $this->assertSame(290360, $stream->position());

        $problematicCue = new HddvdSupCue($stream, $filePath);

        $outputFilePath = $problematicCue->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileSnapshot($outputFilePath);

        $this->assertSame(299982, $stream->position());
    }

    /** @test */
    function it_can_extract_the_image_from_a_cue()
    {
        $filePath = $this->testFilePath.'sup-hddvd/01-section-01.dat';

        $stream = new Stream($filePath);

        $cue = new HddvdSupCue($stream, $filePath);

        $outputFilePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileSnapshot($outputFilePath);
    }

    /** @test */
    function it_can_extract_the_image_from__cue_2()
    {
        $filePath = $this->testFilePath.'sup-hddvd/01.sup';

        $stream = new Stream($filePath);

        new HddvdSupCue($stream, $filePath);

        $cue = new HddvdSupCue($stream, $filePath);

        $outputFilePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileSnapshot($outputFilePath);
    }

    /** @test */
    function it_can_extract_the_image_from__cue_3()
    {
        $filePath = $this->testFilePath.'sup-hddvd/01.sup';

        $stream = new Stream($filePath);

        new HddvdSupCue($stream, $filePath);
        new HddvdSupCue($stream, $filePath);

        $cue = new HddvdSupCue($stream, $filePath);

        $outputFilePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileSnapshot($outputFilePath);
    }
}
