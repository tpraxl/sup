<?php

namespace SjorsO\Sup\Bluray\Sections;

use Exception;
use SjorsO\Sup\Bluray\DataSection;
use SjorsO\Sup\Streams\SupStream;

class FrameSection extends DataSection
{
    /** @var array */
    protected $frames = [];

    public function getSectionIdentifier()
    {
        return "\x17";
    }

    /**
     * @param SupStream $stream stream positioned at the start of the data
     * @return SupStream stream positioned at the end of the data
     * @throws Exception
     */
    protected function readData(SupStream $stream)
    {
        $numberOfFrames = $stream->uint8();

        if ($numberOfFrames !== 1 && $numberOfFrames !== 2) {
            throw new Exception("Unexpected number of frames ({$numberOfFrames})");
        }

        $this->frames[] = $this->readFrame($stream);

        if ($numberOfFrames === 2) {
            $this->frames[] = $this->readFrame($stream);
        }

        return $stream;
    }

    protected function readFrame(SupStream $stream)
    {
        $stream->skip(1);

        return [
            'x'      => $stream->uint16(),
            'y'      => $stream->uint16(),
            'width'  => $stream->uint16(),
            'height' => $stream->uint16(),
        ];
    }

    public function getFrames()
    {
        return $this->frames;
    }
}
