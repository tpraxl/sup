<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Hddvd\HddvdSupCue;
use SjorsO\Sup\Streams\Stream;
use Spatie\Snapshots\MatchesSnapshots;

class HddvdCueTest extends BaseTestCase
{
    use MatchesSnapshots;

    /** @test */
    function it_can_parse_a_cue()
    {
        $filePath = $this->testFilePath.'/sup-hddvd/01-section-01.dat';

        $stream = new Stream($filePath);

        $cue = new HddvdSupCue($stream, $filePath);

        // 00:00:32,565 --> 00:00:36,945
        $this->assertSame(32566, $cue->getStartTime());
        $this->assertSame(36946, $cue->getEndTime());

        $this->assertSame(122, $cue->getWidth(), 'Width is wrong');
        $this->assertSame(471, $cue->getHeight(), 'Height is wrong');

        $this->assertSame(1655, $cue->getX());
        $this->assertSame(164, $cue->getY());

        $this->assertSame(17484, $stream->position());
    }

    /** @test */
    function it_can_parse_the_second_cue()
    {
        $filePath = $this->testFilePath.'/sup-hddvd/01-section-01+02.dat';

        $stream = new Stream($filePath);

        new HddvdSupCue($stream, $filePath);

        $secondCue = new HddvdSupCue($stream, $filePath);

        // 00:03:47,393 --> 00:03:49,566
        $this->assertSame(3*60*1000 + 47394, $secondCue->getStartTime());
        $this->assertSame(3*60*1000 + 49567, $secondCue->getEndTime());

        $this->assertSame(121, $secondCue->getWidth());
        $this->assertSame(436, $secondCue->getHeight());

        $this->assertSame(1651, $secondCue->getX());
        $this->assertSame(166, $secondCue->getY());

        $this->assertSame(33248, $stream->position());
    }

    /** @test */
    function slim_vertical_cue()
    {
        $filePath = $this->testFilePath . '/sup-hddvd/01.sup';

        $stream = new Stream($filePath);

        new HddvdSupCue($stream, $filePath);
        new HddvdSupCue($stream, $filePath);

        $secondCue = new HddvdSupCue($stream, $filePath);

        $this->assertSame(60, $secondCue->getWidth());
        $this->assertSame(572, $secondCue->getHeight());

        $this->assertSame(1717, $secondCue->getX());
        $this->assertSame(172, $secondCue->getY());
    }

    /** @test */
    function landscape_cue()
    {
        $filePath = $this->testFilePath.'/sup-hddvd/01.sup';

        $stream = new Stream($filePath);

        new HddvdSupCue($stream, $filePath);
        new HddvdSupCue($stream, $filePath);
        new HddvdSupCue($stream, $filePath);
        new HddvdSupCue($stream, $filePath);

        $secondCue = new HddvdSupCue($stream, $filePath);

        new HddvdSupCue($stream, $filePath);

        // 00:24:12,117 --> 00:24:13,596
        $this->assertSame(24*60*1000 + 12117, $secondCue->getStartTime());
        $this->assertSame(24*60*1000 + 13596, $secondCue->getEndTime());

        $this->assertSame(514, $secondCue->getWidth());
        $this->assertSame(60, $secondCue->getHeight());

        $this->assertSame(710, $secondCue->getX());
        $this->assertSame(890, $secondCue->getY());
    }

    /** @test */
    function it_can_extract_the_image_from_a_cue()
    {
        $filePath = $this->testFilePath.'/sup-hddvd/01-section-01.dat';

        $stream = new Stream($filePath);

        $cue = new HddvdSupCue($stream, $filePath);

        $outputFilePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileHashSnapshot($outputFilePath);
    }
}
