<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Hddvd\HddvdSupCue;
use SjorsO\Sup\Streams\Stream;

class HddvdCueTest extends BaseTestCase
{
    /** @test */
    function it_can_parse_a_cue()
    {
        $filePath = $this->testFilePath.'/sup-hddvd/01-section-01.dat';

        $stream = new Stream($filePath);

        $cue = new HddvdSupCue($stream, $filePath);

        $this->assertSame(32566, $cue->getStartTime());

        $this->assertSame(1777, $cue->getWidth());
        $this->assertSame(635, $cue->getHeight());

        $this->assertSame(1655, $cue->getX());
        $this->assertSame(164, $cue->getY());

        $this->assertSame(17484, $stream->position());
    }

    /** @test */
    function it_can_parse_the_second_cue()
    {
        $filePath = $this->testFilePath.'/sup-hddvd/01-section-01+02.dat';

        $stream = new Stream($filePath);

        $firstCue = new HddvdSupCue($stream, $filePath);

        $secondCue = new HddvdSupCue($stream, $filePath);

        $this->assertSame(227394, $secondCue->getStartTime());

        $this->assertSame(1772, $secondCue->getWidth());
        $this->assertSame(602, $secondCue->getHeight());

        $this->assertSame(1651, $secondCue->getX());
        $this->assertSame(166, $secondCue->getY());

        $this->assertSame(33248, $stream->position());
    }
}
