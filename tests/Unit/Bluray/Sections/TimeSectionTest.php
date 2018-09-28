<?php

namespace SjorsO\Sup\Tests\Unit\Bluray\Sections;

use SjorsO\Sup\Formats\Bluray\Sections\TimeSection;
use SjorsO\Sup\Tests\BaseTestCase;

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
