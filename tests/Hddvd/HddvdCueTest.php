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

  //  /** @test */
  //  function it_can_parse_the_second_cue()
  //  {
   //     $filePath = $this->testFilePath.'/sup-hddvd/01-section-01+02.dat';
//
   //     $stream = new Stream($filePath);
//
   //     $stream->skip(17484);
//
   //     $cue = new HddvdSupCue($stream, $filePath);
//
   //     $this->assertSame(227394, $cue->getStartTime());


        // 0x83 @ 16432  (palette section)
        // --> 768 bytes of colors
        // total length 769

        // 0x84 @ 17201  (alpha palette)
        // --> 256 bytes of alpha
        // total length 257

        // 0x85 @ 17458  (coordinates)
        // --> 6 bytes
        // every value is 12 bits
        // 1. x
        // 2. width
        // 3. y
        // 4. height

        // 0x86 @ 17465  (data index)
        // --> 8 bytes
        // int32 startOdd;
        // int32 startEven;

        // 0xff @ 17474  (end)
        // --> 0 bytes

//##################################################

        // 0x01 @ 17475
        // --> 5 bytes

        // 0x02 @ 17481
        // --> 2 bytes  (END OF FILE)
   // }
}
