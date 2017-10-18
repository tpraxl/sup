<?php

namespace SjorsO\Sup\Formats;

interface SupCueInterface
{
    public function extractImage($outputDirectory = './', $outputFileName = 'frame.png');

    public function setCueIndex($index);

    public function getCueIndex();

    public function getStartTime();

    public function getEndTime();
}
