<?php

namespace SjorsO\Sup\Bluray\Sections;

use SjorsO\Sup\Bluray\DataSection;
use SjorsO\Sup\Streams\SupStream;

class EndSection extends DataSection
{
    public function getSectionIdentifier()
    {
        return "\x80";
    }

    /**
     * @param SupStream $stream stream positioned at the start of the data
     * @return SupStream stream positioned at the end of the data
     */
    protected function readData(SupStream $stream)
    {
        return $stream;
    }
}
