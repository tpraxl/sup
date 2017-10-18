<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Formats\Bluray\Sections\BitmapSection;

class BitmapSectionTest extends BaseTestCase
{
    /** @test */
    function it_can_read_from_a_bitmap_sections()
    {
        $filePath = $this->testFilePath.'sections/bluray/01-bitmap-section.dat';

        $section = new BitmapSection($filePath);

        $this->assertSame(199, $section->getWidth());
        $this->assertSame(45,  $section->getHeight());
        $this->assertSame(24, $section->getBitmapStart());
        $this->assertSame(3245, $section->getBitmapLength());
        $this->assertSame(null, $section->getSecondBitmapStart());
        $this->assertSame(null, $section->getSecondBitmapLength());
    }
}
