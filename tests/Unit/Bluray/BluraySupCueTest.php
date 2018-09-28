<?php

namespace SjorsO\Sup\Tests\Unit\Bluray;

use SjorsO\Sup\Formats\Bluray\Sections\BitmapSection;
use SjorsO\Sup\Formats\Bluray\Sections\EndSection;
use SjorsO\Sup\Formats\Bluray\Sections\FrameSection;
use SjorsO\Sup\Formats\Bluray\Sections\PaletteSection;
use SjorsO\Sup\Formats\Bluray\Sections\TimeSection;
use SjorsO\Sup\Formats\Bluray\BluraySupCue;
use SjorsO\Sup\Tests\BaseTestCase;

class BluraySupCueTest extends BaseTestCase
{
    /** @test */
    function it_can_extract_an_image_from_a_basic_sup_cue()
    {
        $cue = new BluraySupCue();

        $path = $this->testFilePath.'sections/bluray/01';

        $cue->addSection(new TimeSection($path.'-time-section.dat'));
        $cue->addSection(new FrameSection($path.'-frame-section.dat'));
        $cue->addSection(new PaletteSection($path.'-palette-section.dat'));
        $cue->addSection(new BitmapSection($path.'-bitmap-section.dat'));
        $cue->addSection(new EndSection($path.'-end-section.dat'));

        $filePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileSnapshot($filePath);
    }

    /** @test */
    function it_can_extract_an_image_from_another_basic_sup_cue()
    {
        $cue = new BluraySupCue();

        $path = $this->testFilePath.'sections/bluray/02';

        $cue->addSection(new TimeSection($path.'-time-section.dat'));
        $cue->addSection(new FrameSection($path.'-frame-section.dat'));
        $cue->addSection(new PaletteSection($path.'-palette-section.dat'));
        $cue->addSection(new BitmapSection($path.'-bitmap-section.dat'));
        $cue->addSection(new EndSection($path.'-end-section.dat'));

        $filePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileSnapshot($filePath);
    }

    /** @test */
    function it_can_extract_an_image_from_yet_another_basic_sup_cue()
    {
        $cue = new BluraySupCue();

        $path = $this->testFilePath.'sections/bluray/03';

        $cue->addSection(new TimeSection($path.'-time-section.dat'));
        $cue->addSection(new FrameSection($path.'-frame-section.dat'));
        $cue->addSection(new PaletteSection($path.'-palette-section.dat'));
        $cue->addSection(new BitmapSection($path.'-bitmap-section.dat'));
        $cue->addSection(new EndSection($path.'-end-section.dat'));

        $filePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileSnapshot($filePath);
    }
}
