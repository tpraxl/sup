<?php

namespace SjorsO\Sup\Formats\Bluray\Sections;

use Exception;
use SjorsO\Sup\Formats\Bluray\DataSection;
use SjorsO\Sup\Streams\Stream;

class FrameSection extends DataSection
{
    /** @var array */
    protected $frames = [];

    public function getSectionIdentifier()
    {
        return DataSection::SECTION_FRAME;
    }

    /**
     * @param Stream $stream stream positioned at the start of the data
     * @return Stream stream positioned at the end of the data
     * @throws Exception
     */
    protected function readData(Stream $stream)
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

    protected function readFrame(Stream $stream)
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

    /**
     * @param BitmapSection $bitmapSection
     * @return mixed
     * @throws Exception
     */
    public function getFrameForBitmap(BitmapSection $bitmapSection)
    {
        foreach($this->frames as $frame) {
            if($frame['width'] === $bitmapSection->getWidth() && $frame['height'] === $bitmapSection->getHeight()) {
                return $frame;
            }
        }

        return $this->frames[0];
    }

    public function getCanvasWidth()
    {
        if(count($this->frames) === 1) {
            return $this->frames[0]['width'];
        }

        return max(
            $this->frames[0]['x'] + $this->frames[0]['width'],
            $this->frames[1]['x'] + $this->frames[1]['width']
        );
    }

    public function getCanvasHeight()
    {
        if(count($this->frames) === 1) {
            return $this->frames[0]['height'];
        }

        return max(
            $this->frames[0]['y'] + $this->frames[0]['height'],
            $this->frames[1]['y'] + $this->frames[1]['height']
        );
    }
}
