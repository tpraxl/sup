<?php

namespace SjorsO\Sup\Formats;

interface SupInterface
{
    public function extractImages($outputDirectory = './', $fileNameTemplate = 'frame-%d.png');

    public function getCues();

    public function getCueManifest();
}
