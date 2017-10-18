<?php

namespace SjorsO\Sup\Formats\Bluray\Sections;

use SjorsO\Sup\Formats\Bluray\DataSection;
use SjorsO\Sup\Streams\Stream;

class EndSection extends DataSection
{
    public function getSectionIdentifier()
    {
        return "\x80";
    }

    /**
     * @param Stream $stream stream positioned at the start of the data
     * @return Stream stream positioned at the end of the data
     */
    protected function readData(Stream $stream)
    {
        return $stream;
    }
}
