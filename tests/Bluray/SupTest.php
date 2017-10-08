<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Bluray\Sup;

class SupTest extends BaseTestCase
{
    /** @test */
    function it_can_parse_a_full_file()
    {
        $sup = new Sup($this->testFilePath.'/sup-bluray/sup-01.sup');

        $cues = $sup->getCues();

        $this->assertSame(755, count($cues));

        $outputImagePath = $cues[50]->extractImage($this->tempFilesDirectory);

        $this->assertMatchesFileHashSnapshot($outputImagePath);
    }

    /** @test */
    function it_can_extract_all_images()
    {
        $sup = new Sup($this->testFilePath.'/sup-bluray/sup-01-mini.sup');

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
        $sup = new Sup($this->testFilePath.'/sup-bluray/sup-02-mini.sup');

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
        $sup = new Sup($this->testFilePath.'/sup-bluray/sup-02-mini.sup');

        $manifest = $sup->getCueManifest();

        $this->assertSame([
            [
                'index' => 0,
                'startTime' => 177531,
                'endTime' => 181355,
            ],
            [
                'index' => 1,
                'startTime' => 181615,
                'endTime' => 184615,
            ],
        ], $manifest);
    }
}
