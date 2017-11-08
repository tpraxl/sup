<?php

namespace SjorsO\Sup\Formats\Bluray\Sections;

use Exception;
use SjorsO\Sup\Formats\Bluray\DataSection;
use SjorsO\Sup\Streams\BlurayRleStream;
use SjorsO\Sup\Streams\Stream;

class BitmapSection extends DataSection
{
    protected $width;

    protected $height;

    protected $controlByte;

    protected $bitmapStartPosition;

    protected $bitmapLength;

    protected $secondBitmapStartPosition = null;

    protected $secondBitmapLength = null;

    public function getSectionIdentifier()
    {
        return DataSection::SECTION_BITMAP;
    }

    /**
     * @param Stream $stream stream positioned at the start of the data
     * @return Stream stream positioned at the end of the data
     * @throws Exception
     */
    protected function readData(Stream $stream)
    {
        $stream->skip(3);

        $controlByte = $stream->byte();

        $this->controlByte = $controlByte;

        if($controlByte !== "\xc0") {
            throw new Exception('control byte is not 0xc0');
        }

        if($controlByte !== "\x80") {
            if ($stream->byte() !== "\x00") {
                throw new Exception('The skipped byte is not 0x00');
            }

            $totalLength = $stream->uint16();

            if ($controlByte === "\xc0" && $this->sectionDataLength - $totalLength !== 7) {
                throw new Exception("Single block with unexpected length (data length: {$this->sectionDataLength}, total length: {$totalLength})");
            }

            $this->width = $stream->uint16();

            $this->height = $stream->uint16();

            $this->bitmapStartPosition = $stream->position();

            $this->bitmapLength = $this->sectionDataLength - 11;

            $stream->skip($this->bitmapLength);
        }
        elseif($controlByte === "\x40") {
            $this->secondBitmapStartPosition = $stream->position();

            $this->secondBitmapLength = $this->sectionDataLength - 4;

            $stream->skip($this->secondBitmapLength);
        }
        else {
            throw new Exception("Unknown control byte ({$controlByte})");
        }

        if($controlByte !== "\x40") {
            // mark as second bitmap (???)
        }
        else {
            // $frameIndex++;
        }

        return $stream;
    }

    public function getRleBitmapStream()
    {
        $handle = fopen($this->filePath, 'rb');

        fseek($handle, $this->bitmapStartPosition);

        $data = fread($handle, $this->bitmapLength);

        fclose($handle);


        $memoryHandle = fopen('php://memory', 'rb+');

        fwrite($memoryHandle, $data);

        rewind($memoryHandle);

        return new BlurayRleStream($memoryHandle, 0, $this->bitmapLength);
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getBitmapStart()
    {
        return $this->bitmapStartPosition;
    }

    public function getBitmapLength()
    {
        return $this->bitmapLength;
    }

    public function getSecondBitmapStart()
    {
        return $this->secondBitmapStartPosition;
    }

    public function getSecondBitmapLength()
    {
        return $this->secondBitmapLength;
    }
}
