<?php

namespace SjorsO\Sup\Tests\Unit\Bluray\Sections;

use SjorsO\Sup\Formats\Bluray\Sections\PaletteSection;
use SjorsO\Sup\Tests\BaseTestCase;

class PaletteSectionTest extends BaseTestCase
{
    /** @test */
    function it_can_read_palette_entries_from_a_palette_section()
    {
        $filePath = $this->testFilePath.'sections/bluray/01-palette-section.dat';

        $section = new PaletteSection($filePath);

        $this->assertSame([0, 0, 0, 127], $section->getColor(0));

        $this->assertSame([0, 0, 0, 27], $section->getColor(25));

        $this->assertSame([38, 38, 38, 0], $section->getColor(75));
    }
}
