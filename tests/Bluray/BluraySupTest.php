<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Formats\Bluray\BluraySup;

class BluraySupTest extends BaseTestCase
{
    /** @test */
    function it_can_parse_a_full_file()
    {
        $sup = new BluraySup($this->testFilePath.'sup-bluray/sup-01.sup');

        $cues = $sup->getCues();

        $this->assertSame(755, count($cues));

        $outputImagePath = $cues[50]->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileHashSnapshot($outputImagePath);
    }

    /** @test */
    function it_can_put_the_total_frame_count_in_the_file_name_when_extracting()
    {
        $sup = new BluraySup($this->testFilePath.'sup-bluray/sup-01-mini.sup');

        $outputFilePaths = $sup->extractImages($this->tempFilesDirectory, '%d-%t');

        $this->assertSame(
            $this->tempFilesDirectory.'00001-00024',
            $outputFilePaths[0]
        );
    }

    /** @test */
    function it_can_extract_all_images()
    {
        $sup = new BluraySup($this->testFilePath.'sup-bluray/sup-01-mini.sup');

        $cues = $sup->getCues();

        $this->assertSame(24, count($cues));

        $outputFilePaths = $sup->extractImages($this->tempFilesDirectory);

        $this->assertSame(24, count($outputFilePaths));

        $this->assertMatchesFileHashSnapshot($outputFilePaths[12]);

        foreach($outputFilePaths as $filePath) {
            $this->assertTrue(file_exists($filePath), $filePath.' does not exist');

            $this->assertTrue(filesize($filePath) > 512, basename($filePath).' was not at least 512 bytes big');
        }
    }

    /** @test */
    function it_fills_cues_with_start_and_end_times()
    {
        $sup = new BluraySup($this->testFilePath.'sup-bluray/sup-02-mini.sup');

        $cues = $sup->getCues();

        // File has 3 cues, but the second cue doesn't have an image and gets removed
        $this->assertSame(2, count($cues));

        $firstCue = $cues[0];
        $lastCue = $cues[1];

        // First cue gets its end time from the (removed) second cue
        $this->assertSame(181355, $firstCue->getEndTime());

        $this->assertSame($lastCue->getEndTime(), $lastCue->getStartTime() + 3000);
    }

    /** @test */
    function it_creates_a_cue_manifest()
    {
        $sup = new BluraySup($this->testFilePath.'sup-bluray/sup-02-mini.sup');

        $manifest = $sup->getCueManifest();

        $this->assertSame([
            [
                'index' => 1,
                'startTime' => 177531,
                'endTime' => 181355,
            ],
            [
                'index' => 2,
                'startTime' => 181615,
                'endTime' => 184615,
            ],
        ], $manifest);
    }

    /** @test */
    function it_can_extract_the_image_from_cues_with_multiple_bitmap_sections()
    {
        $sup = new BluraySup($this->testFilePath.'sup-bluray/05-bluray-multi-bitmap-section-mini.sup');

        $cues = $sup->getCues();

        $this->assertSame(11, count($cues));

        $outputFilePath = $cues[10]->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileHashSnapshot($outputFilePath);
    }

    /** @test */
    function it_selects_the_correct_frame_for_the_bitmap_section()
    {
        // The 7th cue of this sup has 2 frames, but only 1 bitmap section

        $sup = new BluraySup($this->testFilePath.'sup-bluray/06-bluray-spanish.sup');

        $cues = $sup->getCues();

        $this->assertSame(73, count($cues));

        $outputFilePaths = $sup->extractImages($this->tempFilesDirectory);

        $this->assertMatchesFileHashSnapshot($outputFilePaths[7]);
    }

    /** @test */
    function if_there_is_only_one_bitmap_section_do_not_use_frame_for_canvas_size()
    {
        // This 6th cue has small single bitmap section, but a large frame.
        // It should use the bitmap size as canvas size

        $sup = new BluraySup($this->testFilePath.'sup-bluray/06-bluray-spanish.sup');

        $cues = $sup->getCues();

        $this->assertSame(73, count($cues));

        $outputFilePath = $cues[6]->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileHashSnapshot($outputFilePath);
    }
}
