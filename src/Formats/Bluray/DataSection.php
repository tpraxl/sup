<?php

namespace SjorsO\Sup\Formats\Bluray;

use Exception;
use SjorsO\Sup\Streams\Stream;

abstract class DataSection
{
    const SECTION_PALETTE = "\x14";
    const SECTION_BITMAP  = "\x15";
    const SECTION_TIME    = "\x16";
    const SECTION_FRAME   = "\x17";
    const SECTION_END     = "\x80";

    protected $filePath;

    protected $startPosition;

    protected $endPosition;

    protected $totalDataLength;

    protected $sectionDataLength;

    protected $firstHeaderInt;

    protected $secondHeaderInt;

    protected $sectionData;

    public function __construct($filePath, $position = 0)
    {
        $this->filePath = $filePath;

        $this->startPosition = $position;

        $supStream = $this->readHeader();

        $supStream = $this->readData($supStream);

        if($supStream->position() !== $this->endPosition) {
            throw new Exception('Stream is not at end position after reading data of ' . (new \ReflectionClass($this))->getShortName() . ". Was at: {$supStream->position()}, should be at: {$this->endPosition}");
        }
    }

    protected function readHeader()
    {
        $stream = new Stream($this->filePath, $this->startPosition, $this->endPosition);

        if($stream->read(2) !== 'PG') {
            throw new Exception('Invalid section header ('.basename($this->filePath)." @ {$this->startPosition})");
        }

        $this->firstHeaderInt = $stream->uint32() / 90;

        $this->secondHeaderInt = $stream->uint32() / 90;

        $sectionIdentifier = $stream->byte();

        if($sectionIdentifier !== $this->getSectionIdentifier()) {
            throw new Exception("Invalid section identifier (was: {$sectionIdentifier}, expected: {$this->getSectionIdentifier()})");
        }

        $this->sectionDataLength = $stream->uint16();

        $this->totalDataLength = 13 + $this->sectionDataLength;

        $this->endPosition = $this->startPosition + $this->totalDataLength;

        return $stream;
    }

    /**
     * @return int total length of bytes in this section
     */
    public function getLength()
    {
        return $this->totalDataLength;
    }

    /**
     * @return string The byte that identifies this section
     */
    public abstract function getSectionIdentifier();

    /**
     * @param Stream $stream stream positioned at the start of the data
     * @return Stream stream positioned at the end of the data
     */
    protected abstract function readData(Stream $stream);

    public function exportDataSection($filePath)
    {
        $handle = fopen($this->filePath, 'rb');

        fseek($handle, $this->startPosition);

        $data = fread($handle, $this->totalDataLength);

        fclose($handle);

        file_put_contents($filePath, $data);
    }
}
