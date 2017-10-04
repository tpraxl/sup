<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Bluray\Sections\PaletteSection;

class PaletteSectionTest extends BaseTestCase
{
    /** @test */
    function it_can_read_palette_entries_from_a_palette_section()
    {
        $filePath = $this->testFilePath.'/sections/bluray/01-palette-section.dat';

        $section = new PaletteSection($filePath);

        $colors = $section->getColors();

        $this->assertSame(252, count($colors));

        $this->assertSame([0, 0, 0, 127], $colors[0]);

        $this->assertSame([0, 0, 0, 27], $colors[25]);

        $this->assertSame([38, 38, 38, 0], $colors[75]);
    }
}
