<?php

namespace SjorsO\Sup\Hddvd;

use Exception;
use SjorsO\Bitstream\BitStream;
use SjorsO\Sup\Streams\Stream;

class HddvdSupCue
{
    protected $filePath;

    protected $stream;

    protected $sectionStartPosition;
    protected $sectionEndPosition = -1;

    protected $startTime;
    protected $endTime;

    protected $startImageOddLines;
    protected $startImageEvenLines;
    protected $imageEvenLinesEndPosition;
    protected $imageOddLinesDataLength;
    protected $imageEvenLinesDataLength;

    protected $colors = [];

    protected $imageX;
    protected $imageY;

    protected $imageWidth;
    protected $imageHeight;

    public function __construct(Stream $stream, $filePath)
    {
        $this->filePath = $filePath;

        $this->stream = $stream;

        $this->sectionStartPosition = $stream->position();

        if($stream->read(2) !== 'SP') {
            throw new Exception('Invalid hd-dvd section identifier');
        }

        $this->startTime = $stream->uint32le();

        $stream->skip(6);

        $firstSequencePosition = $this->sectionStartPosition + 1 + $stream->uint32();

        $secondSequencePosition = $this->sectionStartPosition + 10 + $stream->uint32();

        $this->imageEvenLinesEndPosition = $secondSequencePosition;

        $this->readSequence($firstSequencePosition);

        $this->readSequence($secondSequencePosition);

        $this->stream->seek($this->sectionEndPosition);
    }

    protected function readSequence($position)
    {
        $this->stream->seek($position);

        $timeValue = $this->stream->uint16();

        $this->stream->skip(4);

        $atEndOfSection = false;

        while($atEndOfSection === false) {
            $atEndOfSection = $this->readBlock($timeValue);
        }
    }

    protected function readBlock($timeValue)
    {
        $identifier = $this->stream->byte();

        switch($identifier)
        {
            case "\x01":
                break;
            case "\x02":
                $this->endTime = $this->startTime + (int)((($timeValue << 10) + 1023) / 90);
                break;
            case "\x83":
                for($i = 0; $i < 768; $i += 3) {
                    $y  = $this->stream->uint8() - 16;
                    $cb = $this->stream->uint8() - 128;
                    $cr = $this->stream->uint8() - 128;

                    $this->colors[] = [
                        max(0, min(255, (int)round(1.1644 * $y + 1.596 * $cr))), // red
                        max(0, min(255, (int)round(1.1644 * $y - 0.813 * $cr - 0.391 * $cb))), // green
                        max(0, min(255, (int)round(1.1644 * $y + 2.018 * $cb))), // blue
                    ];
                }
                break;
            case "\x84":
                for($i = 0; $i < 256; $i++) {
                    // alpha: 0 = opaque, 255 = completely transparent
                    $this->colors[$i][] = (int)floor($this->stream->uint8() / 2);
                }
                break;
            case "\x85":
                $bitStream = Bitstream::fromData($this->stream->read(6));

                $this->imageX      = $bitStream->bits(12);
                $this->imageWidth  = $bitStream->bits(12) - $this->imageX + 1;
                $this->imageY      = $bitStream->bits(12);
                $this->imageHeight = $bitStream->bits(12) - $this->imageY + 1;
                break;
            case "\x86":
                $this->startImageOddLines  = $this->sectionStartPosition + 10 + $this->stream->uint32();
                $this->startImageEvenLines = $this->sectionStartPosition + 10 + $this->stream->uint32();

                $this->imageOddLinesDataLength = $this->startImageEvenLines - $this->startImageOddLines;
                $this->imageEvenLinesDataLength = $this->imageEvenLinesEndPosition - $this->startImageEvenLines;
                break;
            case "\xff":
                if($this->stream->position() > $this->sectionEndPosition) {
                    $this->sectionEndPosition = $this->stream->position() + 1;
                }
                return true;
            case "\xc8":
                // unknown identifier
                break;
            default:
                throw new Exception('Unknown block identifier (0x'.bin2hex($identifier).' @ '.$this->stream->position().')');
        }

        return false;
    }

    public function extractImage($outputDirectory = './', $outputFileName = 'frame.png')
    {
        $totalX = $this->getWidth();
        $totalY = $this->getHeight();

        $oddLineBitStream  = new BitStream($this->filePath, $this->startImageOddLines,  $this->imageOddLinesDataLength);
        $evenLineBitStream = new BitStream($this->filePath, $this->startImageEvenLines, $this->imageEvenLinesDataLength);

        $image = imagecreatetruecolor($totalX , $totalY);

        $currentX = 0;
        $currentY = 0;

        while($currentY < $totalY) {

            for($oddAndEven = 0; $oddAndEven < 2; $oddAndEven++) {

                $streamToUse = ($oddAndEven === 0) ? $oddLineBitStream : $evenLineBitStream;

                while($currentX < $totalX) {

                    list($colorIndex, $runLength, $toEndOfLine) = $this->getNextColorRunLength($streamToUse);

                    $fillUntilX = $toEndOfLine ? $totalX : $currentX + $runLength;

                    if($fillUntilX > $totalX) {
                        throw new Exception('Trying to fill beyond end of line (' . $currentX.' + '.$runLength.' = '.($currentX + $runLength).' > '.$totalX.')');
                    }

                    $imageColor = $this->getImageColor($image, $colorIndex);

                    for (; $currentX < $fillUntilX; $currentX++) {
                        imagesetpixel($image, $currentX, $currentY, $imageColor);
                    }
                }

                $streamToUse->skipToNextByte();
                $currentX = 0;
                $currentY++;
            }
        }

        $outputFilePath = rtrim($outputDirectory, '/') . '/' . $outputFileName;

        imagepng($image, $outputFilePath);

        return $outputFilePath;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    public function getWidth()
    {
        return $this->imageWidth;
    }

    public function getHeight()
    {
        return $this->imageHeight;
    }

    public function getX()
    {
        return $this->imageX;
    }

    public function getY()
    {
        return $this->imageY;
    }

    protected function getNextColorRunLength(BitStream $bitStream)
    {
        $hasRunLength = $bitStream->bool();

        $colorIndex = $bitStream->bool() ? $bitStream->bits(8) : $bitStream->bits(2);

        if (!$hasRunLength) {
            return [$colorIndex, 1, false];
        }

        $runLengthSwitch = $bitStream->bool();

        if (!$runLengthSwitch) {
            $pixelCount = $bitStream->bits(3) + 2;

            return [$colorIndex, $pixelCount, false];
        }

        $pixelCount = $bitStream->bits(7);

        if ($pixelCount === 0) {
            return [$colorIndex, $pixelCount, true];
        }

        return [$colorIndex, $pixelCount + 9, false];
    }

    protected function getImageColor($image, $colorIndex)
    {
        list($r, $g, $b, $a) = $this->colors[$colorIndex];

        return imagecolorallocatealpha($image, $r, $g, $b, $a);
    }
}
