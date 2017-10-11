<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Bluray\Sections\TimeSection;

class TimeSectionTest extends BaseTestCase
{
    /** @test */
    function it_can_read_the_header_of_a_time_section()
    {
        $filePath = $this->testFilePath.'sections/bluray/01-time-section.dat';

        $section = new TimeSection($filePath);

        $this->assertSame(32, $section->getLength());

        $this->assertSame(21101, $section->getStartTime());
    }
}
