<?php

namespace SjorsO\Sup\Formats\Dvd;

use Exception;
use SjorsO\Bitstream\BitStream;
use SjorsO\Sup\Streams\DvdRleStream;
use SjorsO\Sup\Streams\Stream;
use SjorsO\Sup\SupCueInterface;

class DvdSupCue implements SupCueInterface
{
    protected $filePath;
    protected $index;

    protected $sectionStartPosition;

    protected $startTime;
    protected $endTime = -1;

    protected $startImageOddLines;
    protected $startImageEvenLines;
    protected $imageOddLinesDataLength;
    protected $imageEvenLinesDataLength;

    protected $colors = [];
    protected $colorAlphas = [];

    protected $imageX;
    protected $imageY;

    protected $imageWidth;
    protected $imageHeight;

    public function __construct(Stream $stream, $filePath)
    {
        $this->filePath = $filePath;

        $this->sectionStartPosition = $stream->position();

        if($stream->read(2) !== 'SP') {
            throw new Exception('Invalid dvd section identifier');
        }

        $this->startTime = $stream->uint32le();

        $stream->skip(4);

        $nextCuePosition  = $this->sectionStartPosition + 10 + $stream->uint16();

        $controlSequencePosition = $this->sectionStartPosition + 10 + $stream->uint16();

        $stream->seek($controlSequencePosition);

        $next = 0;

        while($stream->position() < $nextCuePosition) {
            var_dump('Reading time value...'.' @ '.$stream->position());
            $timeValue = $stream->uint16();

            $nextControlSequencePosition = $stream->position() + $stream->uint16();
            var_dump('time value: '.$timeValue.',  next pos: ' . $nextControlSequencePosition);

            $next = ($nextControlSequencePosition !== $next && $nextControlSequencePosition > $stream->position())
                ? $next = $nextControlSequencePosition
                : $next = $nextCuePosition;

            while($stream->position() < $nextControlSequencePosition && $stream->position() < $nextCuePosition) {
                $identifier = $stream->byte();
                var_dump("\n".'identifier 0x'.bin2hex($identifier).' @ '.$stream->position());
                switch($identifier)
                {
                    case "\x01":
                        break;
                    case "\x02":
                        $stream->skip(4); // UNKNOWN BYTES

                        $this->endTime = $this->startTime + (int)((($timeValue << 10) + 1023) / 90);
                        break;
                    case "\x03":
                        $bitStream = Bitstream::fromData($stream->read(2));

                        $this->colors[0] = $bitStream->bits(4);
                        $this->colors[1] = $bitStream->bits(4);
                        $this->colors[2] = $bitStream->bits(4);
                        $this->colors[3] = $bitStream->bits(4);
                        break;
                    case "\x04":
                        $bitStream = Bitstream::fromData($stream->read(2));

                        $this->colorAlphas[0] = $bitStream->bits(4);
                        $this->colorAlphas[1] = $bitStream->bits(4);
                        $this->colorAlphas[2] = $bitStream->bits(4);
                        $this->colorAlphas[3] = $bitStream->bits(4);
                        break;
                    case "\x05":
                        $bitStream = Bitstream::fromData($stream->read(6));

                        $this->imageX      = $bitStream->bits(12);
                        $this->imageWidth  = $bitStream->bits(12) - $this->imageX + 1;
                        $this->imageY      = $bitStream->bits(12);
                        $this->imageHeight = $bitStream->bits(12) - $this->imageY + 1;
                        break;
                    case "\x06":
                        $this->startImageOddLines = $this->sectionStartPosition + 10 + $stream->uint16();

                        $this->startImageEvenLines = $this->sectionStartPosition + 10 + $stream->uint16();

                        $this->imageOddLinesDataLength = $this->startImageEvenLines - $this->startImageOddLines;

                        $this->imageEvenLinesDataLength = $controlSequencePosition - $this->startImageEvenLines;
                        break;
                    case "\x07":
                        $dataLength = $stream->uint16() - 2;

                        $stream->skip($dataLength);
                        break;
                    case "\xff":
                        break;
                    default:
                        throw new Exception('Unknown block identifier (0x'.bin2hex($identifier).' @ '.$stream->position().')');
                }
            }

        }

        $stream->seek($nextCuePosition);
    }

    public function extractImage($outputDirectory = './', $outputFileName = 'frame.png')
    {
        $totalX = $this->getWidth();
        $totalY = $this->getHeight();

        $oddLineBitStream  = new DvdRleStream($this->filePath, $this->startImageOddLines,  $this->imageOddLinesDataLength);
        $evenLineBitStream = new DvdRleStream($this->filePath, $this->startImageEvenLines, $this->imageEvenLinesDataLength);

        $image = imagecreatetruecolor($totalX , $totalY);

        $currentX = 0;
        $currentY = 0;

        while($currentY < $totalY) {

            for($oddAndEven = 0; $oddAndEven < 2; $oddAndEven++) {

                $rle = ($oddAndEven === 0) ? $oddLineBitStream : $evenLineBitStream;

                while($currentX < $totalX) {
                    $rle->nextRun();

                    $fillUntilX = $rle->toEndOfLine() ? $totalX : $currentX + $rle->runLength();

                    if($fillUntilX > $totalX) {
                        throw new Exception('Trying to fill beyond end of line ('.$currentX.' + '.$rle->runLength().' = '.($currentX + $rle->runLength()).' > '.$totalX.')');
                    }

                    $imageColor = $this->getImageColor($image, $rle->colorIndex());

                    for (; $currentX < $fillUntilX; $currentX++) {
                        imagesetpixel($image, $currentX, $currentY, $imageColor);
                    }
                }

                $rle->skipToNextByte();
                $currentX = 0;

                if(++$currentY === $totalY) {
                    break;
                }
            }
        }

        var_dump("odd line stream  @ {$oddLineBitStream->position()} (should be at: ".($this->startImageOddLines+$this->imageOddLinesDataLength).") (start: {$this->startImageOddLines})");
        var_dump("even line stream @ {$evenLineBitStream->position()} (should be at: ".($this->startImageEvenLines+$this->imageEvenLinesDataLength).") (start: {$this->startImageEvenLines})");

        $outputFilePath = rtrim($outputDirectory, '/') . '/' . $outputFileName;

        imagepng($image, $outputFilePath);

        return $outputFilePath;
    }

    protected function getImageColor($image, $colorIndex)
    {
        static $alphas = null;

        static $colors = null;

        if($alphas === null) {
            $alphas = array_map(function($value) {
                return (int)floor($value / 2);
            }, [0, 16, 32, 48, 64, 80, 96, 128, 144, 160, 176, 192, 208, 224, 240, 255]);
        }

        if($colors === null) {
            $colors = [
                [41, 110, 240],
                [81, 240, 90],
                [16, 128, 128],
                [235, 128, 128],
                [144, 34, 54],
                [106, 222, 202],
                [210, 146, 16],
                [91, 74, 146],
                [123, 128, 128],
                [209, 128, 128],
                [48, 182, 110],
                [78, 82, 92],
                [28, 120, 182],
                [98, 207, 206],
                [136, 179, 59],
                [60, 174, 164],
            ];
        }

        list($r, $g, $b) = $colors[$this->colors[$colorIndex]];

        $a = $alphas[$this->colorAlphas[$colorIndex]];

        return imagecolorallocatealpha($image, $r, $g, $b, $a);
    }

    public function setCueIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    public function getCueIndex()
    {
        return $this->index;
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
}
