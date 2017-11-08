<?php

namespace SjorsO\Sup\Formats\Bluray\Sections;

use Exception;
use SjorsO\Sup\Formats\Bluray\DataSection;
use SjorsO\Sup\Streams\Stream;

class TimeSection extends DataSection
{
    protected $frameRate;

    public function getSectionIdentifier()
    {
        return DataSection::SECTION_TIME;
    }

    public function getStartTime()
    {
        return $this->firstHeaderInt;
    }

    /**
     * @param Stream $stream stream positioned at the start of the data
     * @return Stream stream positioned at the end of the data
     * @throws Exception
     */
    protected function readData(Stream $stream)
    {
        $stream->skip(4);

        $this->frameRate = $stream->byte();

        $stream->skip(5);

        $blockCount = $stream->uint8();

        if($blockCount * 8 + 11 !== $this->sectionDataLength) {
            throw new Exception('Unexpected block count');
        }

        $stream->skip($blockCount * 8);

        return $stream;
    }

    public function getFrameRate()
    {
        return $this->frameRate;
    }
}
