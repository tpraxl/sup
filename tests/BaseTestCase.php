<?php

namespace SjorsO\Sup\Tests;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

abstract class BaseTestCase extends TestCase
{
    use MatchesSnapshots;

    public $testFilePath;

    public $tempFilesDirectory;

    public function setUp()
    {
        parent::setUp();

        $this->testFilePath = __DIR__ . '/files';

        $this->tempFilesDirectory = $this->testFilePath . '/temp/';
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->emptyTempFilesDirectory();
    }

    protected function emptyTempFilesDirectory()
    {
        $fileNames = scandir($this->tempFilesDirectory);

        $fileNames = array_filter($fileNames, function($str) {
            return substr($str, 0, 1) !== '.';
        });

        foreach($fileNames as $name) {
            unlink($this->tempFilesDirectory . $name);
        }
    }

    protected function getSnapshotDirectory(): string
    {
        return $this->testFilePath.DIRECTORY_SEPARATOR.'__snapshots__';
    }
}
