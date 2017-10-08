<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Bluray\Sections\BitmapSection;
use SjorsO\Sup\Bluray\Sections\EndSection;
use SjorsO\Sup\Bluray\Sections\FrameSection;
use SjorsO\Sup\Bluray\Sections\PaletteSection;
use SjorsO\Sup\Bluray\Sections\TimeSection;
use SjorsO\Sup\Bluray\SupCue;
use Spatie\Snapshots\MatchesSnapshots;

class SupCueTest extends BaseTestCase
{
    use MatchesSnapshots;

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

        $this->assertMatchesFileHashSnapshot($filePath);
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

        $this->assertMatchesFileHashSnapshot($filePath);
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

        $this->assertMatchesFileHashSnapshot($filePath);
    }
}
