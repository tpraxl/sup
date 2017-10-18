<?php

namespace SjorsO\Sup\Streams;

class BlurayRleStream extends RleStream
{
    protected function loadNextRun()
    {
        $firstByte = $this->bitStream->bits(8);

        if($firstByte !== 0) {
            $this->runLength = 1;
            $this->colorIndex = $firstByte;
            return;
        }

        $firstBitOn = $this->bitStream->bool();
        $secondBitOn = $this->bitStream->bool();

        $this->runLength = $this->bitStream->bits(6);

        if($secondBitOn) {
            $this->runLength = ($this->runLength << 8) + $this->bitStream->bits(8);
        }

        $this->colorIndex = $firstBitOn ? $this->bitStream->bits(8) : 0;

        $this->toEndOfLine = (!$firstBitOn && !$secondBitOn && $this->runLength == 0);
    }
}
