<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Bluray\Sections\BitmapSection;
use SjorsO\Sup\Bluray\Sections\EndSection;
use SjorsO\Sup\Bluray\Sections\FrameSection;
use SjorsO\Sup\Bluray\Sections\PaletteSection;
use SjorsO\Sup\Bluray\Sections\TimeSection;
use SjorsO\Sup\Bluray\SupCue;

class SupCueTest extends BaseTestCase
{
    /** @test */
    function it_can_extract_an_image_from_a_basic_sup_cue()
    {
        $cue = new SupCue();

        $path = $this->testFilePath.'/sections/bluray/01';

        $cue->addSection(new TimeSection($path.'-time-section.dat'));
        $cue->addSection(new FrameSection($path.'-frame-section.dat'));
        $cue->addSection(new PaletteSection($path.'-palette-section.dat'));
        $cue->addSection(new BitmapSection($path.'-bitmap-section.dat'));
        $cue->addSection(new EndSection($path.'-end-section.dat'));

        $filePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertSame('7ffd6f08daa1a4d876066135a59feef7ff078a4f', sha1_file($filePath));
    }

    /** @test */
    function it_can_extract_an_image_from_another_basic_sup_cue()
    {
        $cue = new SupCue();

        $path = $this->testFilePath.'/sections/bluray/02';

        $cue->addSection(new TimeSection($path.'-time-section.dat'));
        $cue->addSection(new FrameSection($path.'-frame-section.dat'));
        $cue->addSection(new PaletteSection($path.'-palette-section.dat'));
        $cue->addSection(new BitmapSection($path.'-bitmap-section.dat'));
        $cue->addSection(new EndSection($path.'-end-section.dat'));

        $filePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertSame('21178cb42c47aabc42d191e71c34e2dd41a0596d', sha1_file($filePath));
    }

    /** @test */
    function it_can_extract_an_image_from_yet_another_basic_sup_cue()
    {
        $cue = new SupCue();

        $path = $this->testFilePath.'/sections/bluray/03';

        $cue->addSection(new TimeSection($path.'-time-section.dat'));
        $cue->addSection(new FrameSection($path.'-frame-section.dat'));
        $cue->addSection(new PaletteSection($path.'-palette-section.dat'));
        $cue->addSection(new BitmapSection($path.'-bitmap-section.dat'));
        $cue->addSection(new EndSection($path.'-end-section.dat'));

        $filePath = $cue->extractImage($this->tempFilesDirectory);

        $this->assertSame('98f06187048bdd70b906a6fb35b398d907c9975a', sha1_file($filePath));
    }
}
