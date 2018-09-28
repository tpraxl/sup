<?php

namespace SjorsO\Sup\Streams;

class DvdRleStream extends RleStream
{
    public function loadNextRun()
    {
        $length = $this->bitStream->bits(2);

        if ($length > 0) {
            $this->runLength = $length;

            $this->colorIndex = $this->bitStream->bits(2);

            return;
        }

        $numberOfZeros = 1;

        while (($length = $this->bitStream->bits(2)) === 0 && $numberOfZeros < 7) {
            $numberOfZeros++;
        }

        if ($numberOfZeros === 7) {
            $this->toEndOfLine = true;
            $this->runLength = 0;
            $this->colorIndex = $length;

            return;
        }

        while ($numberOfZeros-- > 0) {
            $length = ($length << 2) + $this->bitStream->bits(2);
        }

        $this->runLength = $length;

        $this->colorIndex = $this->bitStream->bits(2);
    }
}
