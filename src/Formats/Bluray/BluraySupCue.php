<?php

namespace SjorsO\Sup\Formats\Bluray;

use Exception;
use RuntimeException;
use SjorsO\Sup\Formats\Bluray\Sections\BitmapSection;
use SjorsO\Sup\Formats\Bluray\Sections\FrameSection;
use SjorsO\Sup\Formats\Bluray\Sections\PaletteSection;
use SjorsO\Sup\Formats\Bluray\Sections\TimeSection;
use SjorsO\Sup\Formats\SupCueInterface;

class BluraySupCue implements SupCueInterface
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

    public function getCueIndex()
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

            if (! $timeSection) {
                throw new RuntimeException('Bluray cue has no time section');
            }

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

        return $this;
    }

    /**
     * @return BitmapSection[]
     */
    protected function getBitmapSections()
    {
        return array_values(array_filter($this->dataSections, function(DataSection $section) {
            return $section->getSectionIdentifier() === DataSection::SECTION_BITMAP;
        }));
    }

    /**
     * @return PaletteSection
     */
    protected function getPaletteSection()
    {
        $paletteSections = array_values(array_filter($this->dataSections, function(DataSection $section) {
            return $section->getSectionIdentifier() === DataSection::SECTION_PALETTE;
        }));

        return $paletteSections[0] ?? null;
    }

    /**
     * @return TimeSection
     */
    protected function getTimeSection()
    {
        $timeSections = array_values(array_filter($this->dataSections, function(DataSection $section) {
            return $section->getSectionIdentifier() === DataSection::SECTION_TIME;
        }));

        return $timeSections[0] ?? null;
    }

    /**
     * @return FrameSection
     */
    protected function getFrameSection()
    {
        $frameSections = array_values(array_filter($this->dataSections, function(DataSection $section) {
            return $section->getSectionIdentifier() === DataSection::SECTION_FRAME;
        }));

        return $frameSections[0] ?? null;
    }

    public function containsImage()
    {
        $paletteSection = $this->getPaletteSection();
        $bitmapSections = $this->getBitmapSections();

        return count($bitmapSections) > 0 && $paletteSection !== null && $paletteSection->hasColors();
    }

    public function extractImage($outputDirectory = './', $outputFileName = 'frame.png')
    {
        $bitmapSections = $this->getBitmapSections();
        $paletteSection = $this->getPaletteSection();
        $frameSection   = $this->getFrameSection();

        $singleBitmap = count($bitmapSections) === 1;

        $image = imagecreatetruecolor(
            $singleBitmap ? $bitmapSections[0]->getWidth()  : $frameSection->getCanvasWidth(),
            $singleBitmap ? $bitmapSections[0]->getHeight() : $frameSection->getCanvasHeight()
        );

        for($bitmapCount = 0; $bitmapCount < count($bitmapSections); $bitmapCount++) {
            $bitmapSection = $bitmapSections[$bitmapCount];

            $frame = $frameSection->getFrameForBitmap($bitmapSection);

            $currentX = $singleBitmap ? 0 : $frame['x'];
            $currentY = $singleBitmap ? 0 : $frame['y'];

            $frameTotalX = $currentX + $bitmapSection->getWidth();
            $frameTotalY = $currentY + $bitmapSection->getHeight();

            $stream = $bitmapSection->getRleBitmapStream();

            while($currentY < $frameTotalY) {
                $stream->nextRun();

                $color = $paletteSection->getImageColor($stream->colorIndex(), $image);

                $fillUntilX = $stream->toEndOfLine() ? $frameTotalX : ($currentX + $stream->runLength());

                if($fillUntilX > $frameTotalX) {
                    throw new Exception('Bluray run length error: trying to fill beyond end of line');
                }

                for(; $currentX < $fillUntilX; $currentX++) {
                    imagesetpixel($image, $currentX, $currentY, $color);
                }

                if($stream->toEndOfLine()) {
                    $currentX = $singleBitmap ? 0 : $frame['x'];
                    $currentY++;
                }
            }
        }

        $outputFilePath = rtrim($outputDirectory, '/') . '/' . $outputFileName;

        imagepng($image, $outputFilePath);

        imagedestroy($image);

        return $outputFilePath;
    }
}
