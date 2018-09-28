<?php

namespace SjorsO\Sup\Tests;

use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SuiteBootstrap implements BeforeFirstTestHook, AfterLastTestHook
{
    protected $tempDirectory = './tests/temp/';

    public function executeBeforeFirstTest(): void
    {
        if (! file_exists($this->tempDirectory)) {
            mkdir($this->tempDirectory);
        }
    }

    public function executeAfterLastTest(): void
    {
        $directoryIterator = new RecursiveDirectoryIterator($this->tempDirectory, RecursiveDirectoryIterator::SKIP_DOTS);

        $fileIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach($fileIterator as $file) {
            $file->isDir()
                ? rmdir($file->getRealPath())
                : unlink($file->getRealPath());
        }

        rmdir($this->tempDirectory);
    }
}
