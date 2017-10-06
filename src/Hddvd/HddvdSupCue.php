<?php

namespace SjorsO\Sup\Hddvd;

use Exception;
use SjorsO\Sup\Streams\Stream;

class HddvdSupCue
{
    protected $stream;

    protected $sectionStartPosition;
    protected $sectionEndPosition = -1;

    protected $startTime;
    protected $endTimeOffset;

    protected $startImageOddLines;
    protected $startImageEvenLines;

    protected $colors = [];

    protected $imageX;
    protected $imageY;

    protected $imageWidth;
    protected $imageHeight;

    public function __construct(Stream $stream, $filePath)
    {
        $this->stream = $stream;

        $this->sectionStartPosition = $stream->position();

        if($stream->read(2) !== 'SP') {
            throw new Exception('Invalid hd-dvd section identifier');
        }

        $this->startTime = $stream->uint32le();

        $stream->skip(6);

        $firstSectionPosition = $this->sectionStartPosition + 1 + $stream->uint32();

        $secondSectionPosition = $this->sectionStartPosition + 10 + $stream->uint32();

        $this->readSection($firstSectionPosition);

        $this->readSection($secondSectionPosition);

        $this->stream->seek($this->sectionEndPosition);
    }

    protected function readSection($position)
    {
        $this->stream->seek($position);

        $timeValue = $this->stream->uint16();

        // $nextBlockPosition = $this->stream->uint32le();
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
                $this->endTimeOffset = $timeValue;
                break;
            case "\x83":
                for($i = 0; $i < 768; $i += 3) {
                    $y  = $this->stream->uint8();
                    $cb = $this->stream->uint8();
                    $cr = $this->stream->uint8();

                    $this->colors[] = [
                        $r = max(0, min(255, (int)round(1.1644 * $y + 1.596 * $cr))),
                        $g = max(0, min(255, (int)round(1.1644 * $y - 0.813 * $cr - 0.391 * $cb))),
                        $b = max(0, min(255, (int)round(1.1644 * $y + 2.018 * $cb))),
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
                $bytes = $this->stream->uint8s(6);
                // values stored per 12 bits
                $this->imageX      = (($bytes[1] & 0b11110000) >> 4) + ($bytes[0] << 4);
                $this->imageWidth  = (($bytes[1] & 0b00001111) << 8) + $bytes[2];
                $this->imageY      = (($bytes[4] & 0b11110000) >> 4) + ($bytes[3] << 4);
                $this->imageHeight = (($bytes[4] & 0b00001111) << 8) + $bytes[5];
                break;
            case "\x86":
                $this->startImageOddLines  = $this->sectionStartPosition + 10 + $this->stream->uint32();
                $this->startImageEvenLines = $this->sectionStartPosition + 10 + $this->stream->uint32();
                break;
            case "\xff":
                if($this->stream->position() > $this->sectionEndPosition) {
                    $this->sectionEndPosition = $this->stream->position() + 1;
                }
                return true;
            default:
                throw new Exception('Unknown block identifier (0x' . bin2hex($identifier) . ' @ ' . $this->stream->position() . ')');
        }

        return false;
    }

    public function getStartTime()
    {
        return $this->startTime;
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
