<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\Bluray\BluraySup;
use SjorsO\Sup\Hddvd\HddvdSup;
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

        $this->assertTrue($sup instanceof HddvdSup);


        $anotherSup = SupFile::open($this->testFilePath.'sup-bluray/sup-01-mini.sup');

        $this->assertTrue($anotherSup instanceof SupInterface);

        $this->assertTrue($anotherSup instanceof BluraySup);
    }
}
