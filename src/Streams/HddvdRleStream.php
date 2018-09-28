<?php

namespace SjorsO\Sup\Streams;

class HddvdRleStream extends RleStream
{
    public function loadNextRun()
    {
        $hasRunLength = $this->bitStream->bool();

        $this->colorIndex = $this->bitStream->bool()
            ? $this->bitStream->bits(8)
            : $this->bitStream->bits(2);

        $this->runLength = $hasRunLength ? $this->figureOutRunLength() : 1;

        $this->toEndOfLine = $this->runLength === 0;
    }

    protected function figureOutRunLength()
    {
        $runLengthSwitch = $this->bitStream->bool();

        if (! $runLengthSwitch) {
            return $this->bitStream->bits(3) + 2;
        }

        $runLength = $this->bitStream->bits(7);

        return $runLength === 0 ? 0 : $runLength + 9;
    }
}
