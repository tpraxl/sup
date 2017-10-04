<?php

namespace SjorsO\Sup\Streams;

use Exception;

class RleStream
{
    protected $handle;

    protected $dataLength;

    protected $counter = 0;

    public function __construct($resource, $start, $dataLength)
    {
        if(is_resource($resource)) {
            $this->handle = $resource;
        }
        else {
            $this->handle = fopen($resource, 'rb');
        }

        fseek($this->handle, $start);

        $this->dataLength = $dataLength;
    }

    /**
     * @return array
     */
    public function readNext()
    {
        $firstByte = $this->uint8();

        if($firstByte != 0) {
            return $this->toReturnValue($firstByte, 1, false);
        }

        list($firstBitOn, $secondBitOn, $runLength) = array_values($this->encodedByte());

        if($firstBitOn) {
            $runLength = ($runLength << 8) + $this->uint8();
        }

        $paletteIndex = $secondBitOn ? $this->uint8() : 0;

        $toEndOfLine = (!$secondBitOn && !$firstBitOn && $runLength == 0);

        return $this->toReturnValue($paletteIndex, $runLength, $toEndOfLine);
    }

    public function uint8()
    {
        return unpack("C", $this->read(1))[1];
    }

    public function encodedByte()
    {
        $byte = $this->uint8();

        $firstTwoBits = ($byte & 0b11000000) >> 6;

        $runLength = 0;
        $runLength = ($runLength << 2) + (($byte & 0b00110000) >> 4);
        $runLength = ($runLength << 2) + (($byte & 0b00001100) >> 2);
        $runLength = ($runLength << 2) +  ($byte & 0b00000011);

        return [
            'firstBitOn'  => ($firstTwoBits & 0b01) != 0,
            'secondBitOn' => ($firstTwoBits & 0b10) != 0,
            'runLength'   => $runLength,
        ];
    }

    protected function read($length)
    {
        if($this->counter + $length > $this->dataLength) {
            throw new Exception("Trying to read more bytes than are available ({$this->counter} + {$length} > {$this->dataLength})");
        }

        $this->counter += $length;

        return fread($this->handle, $length);
    }

    public function position()
    {
        return ftell($this->handle);
    }

    protected function toReturnValue($paletteIndex, $runLength, $tillEndOfLine)
    {
        return [
            'paletteIndex' => $paletteIndex,
            'runLength'    => (int)$runLength,
            'toEndOfLine'  => (bool)$tillEndOfLine,
        ];
    }
}
