<?php

namespace SjorsO\Sup\Streams;

use SjorsO\Bitstream\BitStream;

class HddvdRleStream implements RleStreamInterface
{
    protected $bitStream;

    protected $runLength;

    protected $colorIndex;

    protected $toEndOfLine;

    public function __construct($resource, $startPosition = 0, $endPosition = null)
    {
        $this->bitStream = new BitStream($resource, $startPosition, $endPosition);
    }

    public function nextRun()
    {
        $hasRunLength = $this->bitStream->bool();

        $this->colorIndex = $this->bitStream->bool() ? $this->bitStream->bits(8) : $this->bitStream->bits(2);

        $this->runLength = $hasRunLength ? $this->figureOutRunLength() : 1;

        $this->toEndOfLine = ($this->runLength === 0);
    }

    protected function figureOutRunLength()
    {
        $runLengthSwitch = $this->bitStream->bool();

        if (! $runLengthSwitch) {
            return $this->bitStream->bits(3) + 2;
        }

        $runLength = $this->bitStream->bits(7);

        return ($runLength === 0) ? 0 : $runLength + 9;
    }

    public function runLength()
    {
        return $this->runLength;
    }

    public function colorIndex()
    {
        return $this->colorIndex;
    }

    public function toEndOfLine()
    {
        return $this->toEndOfLine;
    }

    public function skipToNextByte()
    {
        $this->bitStream->skipToNextByte();
    }
}
