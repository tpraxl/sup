<?php

namespace SjorsO\Sup\Bluray\Sections;

use Exception;
use SjorsO\Sup\Bluray\DataSection;
use SjorsO\Sup\Streams\Stream;

class PaletteSection extends DataSection
{
    /** @var array [ index => [r, g, b, alpha], ...] */
    protected $colors = [];

    public function getSectionIdentifier()
    {
        return "\x14";
    }

    /**
     * @param Stream $stream stream positioned at the start of the data
     * @return Stream stream positioned at the end of the data
     * @throws Exception
     */
    protected function readData(Stream $stream)
    {
        $stream->skip(2);

        if($this->sectionDataLength % 5 !== 2) {
            throw new Exception('Invalid palette data length');
        }

        $paletteEntries = ($this->sectionDataLength - 2) / 5;

        for($i = 0; $i < $paletteEntries; $i++) {
            $index = $stream->uint8();

            $y = $stream->uint8() - 16;
            $cb = $stream->uint8() - 128;
            $cr = $stream->uint8() - 128;

            // 0 = transparent, 255 = opaque
            $alpha = $stream->uint8();

            $r = max(0, min(255, (int)round(1.1644 * $y + 1.596 * $cr)));
            $g = max(0, min(255, (int)round(1.1644 * $y - 0.813 * $cr - 0.391 * $cb)));
            $b = max(0, min(255, (int)round(1.1644 * $y + 2.018 * $cb)));

            // convert to 0-127, where 0 = opaque, 127 = transparent
            $alpha = ((int)(substr($alpha - 255, 1))) >> 1;

            $this->colors[$index] = [$r, $g, $b, $alpha];
        }

        return $stream;
    }

    public function getColors()
    {
        return $this->colors;
    }

    public function hasColors()
    {
        return count($this->colors) > 0;
    }

    /**
     * @param $index
     * @return array
     * @throws Exception
     */
    public function getColor($index)
    {
        if(!isset($this->colors[$index])) {
            return [0, 0, 0, 127];
        }

        return $this->colors[$index];
    }

    public function getImageColor($index, &$image)
    {
        list($r, $g, $b, $a) = $this->getColor($index);

        return imagecolorallocatealpha($image, $r, $g, $b, $a);
    }
}
