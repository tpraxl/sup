<?php

namespace SjorsO\Sup\Tests\Unit\Hddvd;

use SjorsO\Sup\Formats\Hddvd\HddvdSup;
use SjorsO\Sup\Tests\BaseTestCase;

class HddvdSupTest extends BaseTestCase
{
    /** @test */
    function it_it_can_extract_all_images_from_a_hddvd_sup()
    {
        $sup = new HddvdSup($this->testFilePath.'sup-hddvd/01.sup');

        $filePaths = $sup->extractImages($this->tempFilesDirectory);

        $this->assertSame(113, count($filePaths));

        foreach($filePaths as $filePath) {
            $this->assertFileExists($filePath);

            $this->assertTrue(filesize($filePath) > 512, basename($filePath).' was smaller than 512 bytes');
        }

        $this->assertMatchesFileSnapshot($filePaths[112]);
    }
}
