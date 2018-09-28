<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Formats\Dvd\DvdSup;

class DvdSupTest extends BaseTestCase
{
    /** @test */
    function it_can_extract_all_images()
    {
        $this->markTestSkipped('dvd sup format does not work yet');

        $sup = new DvdSup($this->testFilePath.'sup-dvd/01.sup');

        $outputFilePaths = $sup->extractImages($this->tempFilesDirectory);

        $this->assertSame(24, count($outputFilePaths));

        $this->assertMatchesFileSnapshot($outputFilePaths[6]);

        foreach($outputFilePaths as $filePath) {
            $this->assertTrue(file_exists($filePath), $filePath.' does not exist');

            $this->assertTrue(filesize($filePath) > 512, basename($filePath).' was not at least 512 bytes big');
        }
    }
}
