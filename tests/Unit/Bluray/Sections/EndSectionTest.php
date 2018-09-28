<?php

namespace SjorsO\Sup\Tests\Unit\Bluray\Sections;

use SjorsO\Sup\Formats\Bluray\Sections\EndSection;
use SjorsO\Sup\Tests\BaseTestCase;

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
