<?php

namespace SjorsO\Sup\Streams;

interface RleStreamInterface
{
    public function __construct($resource, $startPosition = 0, $endPosition = null);

    public function nextRun();

    public function runLength();

    public function colorIndex();

    public function toEndOfLine();

    public function skipToNextByte();
}
