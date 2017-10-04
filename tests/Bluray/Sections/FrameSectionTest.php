<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Bluray\Sections\FrameSection;

class FrameSectionTest extends BaseTestCase
{
    /** @test */
    function it_can_read_a_section_with_one_frame()
    {
        $filePath = $this->testFilePath.'/sections/bluray/01-frame-section.dat';

        $section = new FrameSection($filePath);

        $frames = $section->getFrames();

        $this->assertSame(1, count($frames));

        $this->assertSame([
            'x' => 540,
            'y' => 615,
            'width'  => 199,
            'height' => 45,
        ], $frames[0]);
    }
}
