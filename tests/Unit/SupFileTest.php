<?php

namespace SjorsO\Sup\Tests\Unit;

use SjorsO\Sup\Formats\Bluray\BluraySup;
use SjorsO\Sup\Formats\Dvd\DvdSup;
use SjorsO\Sup\Formats\Hddvd\HddvdSup;
use SjorsO\Sup\SupFile;
use SjorsO\Sup\Formats\SupInterface;
use SjorsO\Sup\Tests\BaseTestCase;

class SupFileTest extends BaseTestCase
{
    /** @test */
    function it_returns_false_for_files_that_are_not_sups()
    {
        $sup = SupFile::open($this->baseTestPath.'BaseTestCase.php');

        $this->assertFalse($sup);
    }

    /** @test */
    function it_can_handle_files_smaller_than_two_bytes()
    {
        $sup = SupFile::open($this->testFilePath.'empty.sup');

        $this->assertFalse($sup);
    }

    /** @test */
    function it_identifies_formats()
    {
        $sup = SupFile::getFormat($this->testFilePath.'sup-dvd/01.sup');

        $this->assertSame(DvdSup::class, $sup);
    }

    /** @test */
    function it_identifies_bluray_sups()
    {
        $sup = SupFile::open($this->testFilePath.'sup-bluray/sup-01-mini.sup');

        $this->assertInstanceOf(SupInterface::class, $sup);

        $this->assertInstanceOf(BluraySup::class, $sup);
    }

    /** @test */
    function it_identifies_hddvd_sups()
    {
        $sup = SupFile::open($this->testFilePath.'sup-hddvd/01.sup');

        $this->assertInstanceOf(SupInterface::class, $sup);

        $this->assertInstanceOf(HddvdSup::class, $sup);
    }

    /** @test */
    function it_identifies_dvd_sups()
    {
        $sup = SupFile::open($this->testFilePath.'sup-dvd/01-section-01.dat');

        $this->assertInstanceOf(SupInterface::class, $sup);

        $this->assertInstanceOf(DvdSup::class, $sup);
    }
}
