<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Formats\Bluray\BluraySup;
use SjorsO\Sup\Formats\Dvd\DvdSup;
use SjorsO\Sup\Formats\Hddvd\HddvdSup;
use SjorsO\Sup\SupFile;
use SjorsO\Sup\SupInterface;

class SupFileTest extends BaseTestCase
{
    /** @test */
    function it_returns_false_for_files_that_are_not_sups()
    {
        $sup = SupFile::open($this->testFilePath.'temp/.gitignore');

        $this->assertFalse($sup);
    }

    /** @test */
    function it_can_handle_files_smaller_than_two_bytes()
    {
        $sup = SupFile::open($this->testFilePath.'empty.sup');

        $this->assertFalse($sup);
    }

    /** @test */
    function it_returns_a_sup_interface()
    {
        $sup = SupFile::open($this->testFilePath.'sup-hddvd/01.sup');

        $this->assertTrue($sup instanceof SupInterface);

        $this->assertTrue($sup instanceof HddvdSup, 'sup not instance of HddvdSup');


        $anotherSup = SupFile::open($this->testFilePath.'sup-bluray/sup-01-mini.sup');

        $this->assertTrue($anotherSup instanceof SupInterface);

        $this->assertTrue($anotherSup instanceof BluraySup, 'sup not instance of BluraySup');


        $yetAnotherSup = SupFile::open($this->testFilePath.'sup-dvd/01-section-01.dat');

        $this->assertTrue($yetAnotherSup instanceof SupInterface);

        $this->assertTrue($yetAnotherSup instanceof DvdSup, 'sup not instance of DvdSup');
    }
}
