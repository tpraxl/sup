<?php

namespace SjorsO\Sup\Tests;

use SjorsO\Sup\SupFile;

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
}
