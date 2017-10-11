<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Bluray\Sections\EndSection;

class EndSectionTest extends BaseTestCase
{
    /** @test */
    function it_can_read_an_end_section()
    {
        $filePath = $this->testFilePath.'sections/bluray/01-end-section.dat';

        $section = new EndSection($filePath);

        $this->assertSame(13, $section->getLength());
    }
}
