<?php

namespace SjorsO\Sup\Streams;

abstract class RleStream
{
    protected $bitStream;

    protected $runLength;

    protected $colorIndex;

    protected $toEndOfLine;

    public function __construct($resource, $startPosition = 0, $endPosition = null)
    {
        $this->bitStream = new BitStream($resource, $startPosition, $endPosition);
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

    public function position()
    {
        return $this->bitStream->position();
    }

    public final function nextRun()
    {
        $this->runLength = null;

        $this->colorIndex = null;

        $this->toEndOfLine = false;

        $this->loadNextRun();
    }

    protected abstract function loadNextRun();
}
