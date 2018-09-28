<?php

namespace SjorsO\Sup\Tests;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

abstract class BaseTestCase extends TestCase
{
    use MatchesSnapshots;

    public $baseTestPath;

    public $testFilePath;

    public $tempFilesDirectory;

    public function setUp()
    {
        parent::setUp();

        $this->baseTestPath = rtrim(__DIR__, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        $this->testFilePath = $this->baseTestPath.'Files/';

        $this->tempFilesDirectory = $this->baseTestPath.'temp/'.bin2hex(random_bytes(32)).'/';

        mkdir($this->tempFilesDirectory);
    }

    protected function getSnapshotDirectory(): string
    {
        return $this->baseTestPath.'_snapshots_';
    }

    protected function getFileSnapshotDirectory(): string
    {
        return $this->baseTestPath.'_snapshots_';
    }
}
