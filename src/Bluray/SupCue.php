<?php

namespace SjorsO\Sup\Bluray;

use Exception;
use SjorsO\Sup\Bluray\Sections\BitmapSection;
use SjorsO\Sup\Bluray\Sections\PaletteSection;
use SjorsO\Sup\Bluray\Sections\TimeSection;

class SupCue
{
    protected $cueIndex = 0;

    protected $startTime = null;

    /**
     * @var null
     */
    protected $endTime = null;

    protected $dataSections = [];

    public function setCueIndex($int)
    {
        $this->cueIndex = $int;

        return $this;
    }

    public function getIndex()
    {
        return $this->cueIndex;
    }

    /**
     * @return int End time in milliseconds
     */
    public function getStartTime()
    {
        if($this->startTime === null) {
            $timeSection = $this->getTimeSection();

            $this->startTime = $timeSection->getStartTime();
        }

        return $this->startTime;
    }

    public function setEndTime($int)
    {
        $this->endTime = $int;

        return $this;
    }

    /**
     * @return int End time in milliseconds
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    public function addSection(DataSection $dataSection)
    {
        $this->dataSections[] = $dataSection;
    }

    /**
     * @return BitmapSection[]
     */
    protected function getBitmapSections()
    {
        return array_values(array_filter($this->dataSections, function(DataSection $section) {
            return $section->getSectionIdentifier() === "\x15";
        }));
    }

    /**
     * @return PaletteSection
     */
    protected function getPaletteSection()
    {
        $paletteSections = array_values(array_filter($this->dataSections, function(DataSection $section) {
            return $section->getSectionIdentifier() === "\x14";
        }));

        return $paletteSections[0] ?? null;
    }

    /**
     * @return TimeSection
     */
    protected function getTimeSection()
    {
        $timeSections = array_values(array_filter($this->dataSections, function(DataSection $section) {
            return $section->getSectionIdentifier() === "\x16";
        }));

        return $timeSections[0] ?? null;
    }

    public function containsImage()
    {
        $paletteSection = $this->getPaletteSection();

        return $paletteSection !== null && $paletteSection->hasColors();
    }

    public function extractImage($outputDirectory = './', $outputFileName = 'frame.png')
    {
        $bitmapSections = $this->getBitmapSections();
        $paletteSection = $this->getPaletteSection();

        if(count($bitmapSections) !== 1) {
            throw new \Exception('Not implemented yet, more than one bitmap section');
        }

        $bitmapSection = $bitmapSections[0];

        $totalX = $bitmapSection->getWidth();
        $totalY = $bitmapSection->getHeight();

        $stream = $bitmapSection->getRleBitmapStream();

        $image = imagecreatetruecolor($totalX , $totalY);

        $currentX = 0;
        $currentY = 0;

        while($currentY < $totalY) {
            list($paletteIndex, $runLength, $toEndOfLine) = array_values($stream->readNext());

            $color = $paletteSection->getImageColor($paletteIndex, $image);

            $fillUntilX = $toEndOfLine ? $totalX : ($currentX + $runLength);

            if($fillUntilX > $totalX) {
                throw new Exception('Trying to fill beyond end of line');
            }

            for(; $currentX < $fillUntilX; $currentX++) {
                imagesetpixel($image, $currentX, $currentY, $color);
            }

            if($toEndOfLine) {
                $currentX = 0;
                $currentY++;
            }
        }

        $outputFilePath = rtrim($outputDirectory, '/') . '/' . $outputFileName;

        imagepng($image, $outputFilePath);

        return $outputFilePath;
    }
}
