<?php

namespace SjorsO\Sup\Formats\Bluray\Sections;

use Exception;
use SjorsO\Sup\Formats\Bluray\DataSection;
use SjorsO\Sup\Streams\Stream;

class PaletteSection extends DataSection
{
    /** @var array [ index => [r, g, b, alpha], ...] */
    protected $colors = [];

    protected $isLazy = false;

    protected $lazyLoadClosure = null;

    public function getSectionIdentifier()
    {
        return DataSection::SECTION_PALETTE;
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

        $paletteEntriesCount = ($this->sectionDataLength - 2) / 5;

        if($paletteEntriesCount === 0) {
            return $stream;
        }

        $bytes = $stream->read($paletteEntriesCount * 5);

        $this->isLazy = true;

        $this->lazyLoadClosure = function() use ($bytes) {
            $bytes = array_map(function($byte) {
                // change all bytes to uint8
                return unpack('C', $byte)[1];
            }, str_split($bytes));

            $colors = [];

            for($i = 0; $i < count($bytes); $i += 5) {
                $index = $bytes[$i];

                $y  = $bytes[$i+1] - 16;
                $cb = $bytes[$i+2] - 128;
                $cr = $bytes[$i+3] - 128;

                // 0 = transparent, 255 = opaque
                $alpha = $bytes[$i+4];

                $r = max(0, min(255, (int)round(1.1644 * $y + 1.596 * $cr)));
                $g = max(0, min(255, (int)round(1.1644 * $y - 0.813 * $cr - 0.391 * $cb)));
                $b = max(0, min(255, (int)round(1.1644 * $y + 2.018 * $cb)));

                // convert to 0-127, where 0 = opaque, 127 = transparent
                $alpha = ((int)(substr($alpha - 255, 1))) >> 1;

                $colors[$index] = [$r, $g, $b, $alpha];
            }

            return $colors;
        };

        return $stream;
    }

    public function hasColors()
    {
        if($this->isLazy) {
            return true;
        }

        return count($this->colors) > 0;
    }

    /**
     * @param $index
     * @return array
     * @throws Exception
     */
    public function getColor($index)
    {
        if($this->isLazy) {
            $this->colors = ($this->lazyLoadClosure)();

            $this->lazyLoadClosure = null;

            $this->isLazy = false;
        }

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
