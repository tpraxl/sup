<?php

namespace SjorsO\Sup\Formats;

interface SupInterface
{
    public function extractImages($outputDirectory = './', $fileNameTemplate = 'frame-[%d-%t].png');

    public function extractImage($index, $outputDirectory = './', $fileNameTemplate = 'frame-[%d-%t].png');

    public function getCues();

    public function cues();

    public function cueIndexes();

    public function getCueManifest();
}
