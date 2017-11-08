<?php

namespace SjorsO\Sup\Formats\Bluray;

use Exception;
use SjorsO\Sup\Formats\Bluray\Sections\BitmapSection;
use SjorsO\Sup\Formats\Bluray\Sections\EndSection;
use SjorsO\Sup\Formats\Bluray\Sections\FrameSection;
use SjorsO\Sup\Formats\Bluray\Sections\PaletteSection;
use SjorsO\Sup\Formats\Bluray\Sections\TimeSection;
use SjorsO\Sup\Streams\Stream;

class BluraySection
{
    private function __construct()
    {
    }

    /**
     * @param Stream $stream stream positioned after section header (PG)
     * @param $filePath
     * @return DataSection
     * @throws Exception
     */
    public static function get(Stream $stream, $filePath)
    {
        $stream->skip(8);

        $sectionIdentifier = $stream->byte();

        $stream->rewind(11);

        switch($sectionIdentifier)
        {
            case DataSection::SECTION_TIME:
                $section = new TimeSection($filePath, $stream->position());
                break;
            case DataSection::SECTION_FRAME:
                $section = new FrameSection($filePath, $stream->position());
                break;
            case DataSection::SECTION_PALETTE:
                $section = new PaletteSection($filePath, $stream->position());
                break;
            case DataSection::SECTION_BITMAP:
                $section = new BitmapSection($filePath, $stream->position());
                break;
            case DataSection::SECTION_END:
                $section = new EndSection($filePath, $stream->position());
                break;
            default:
                throw new Exception('Unknown bluray section identifier (0x'.bin2hex($sectionIdentifier).')');
        }

        $stream->skip($section->getLength());

        return $section;
    }
}
