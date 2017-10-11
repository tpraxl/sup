<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Hddvd\HddvdSup;

class HddvdSupTest extends BaseTestCase
{
    /** @test */
    function it_it_can_extract_all_images_from_a_hddvd_sup()
    {
        $sup = new HddvdSup($this->testFilePath.'/sup-hddvd/01.sup');

        $filePaths = $sup->extractImages($this->tempFilesDirectory);

        exit;
    }
}
