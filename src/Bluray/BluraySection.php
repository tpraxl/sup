<?php

namespace SjorsO\Sup\Bluray;

use Exception;
use SjorsO\Sup\Bluray\Sections\BitmapSection;
use SjorsO\Sup\Bluray\Sections\EndSection;
use SjorsO\Sup\Bluray\Sections\FrameSection;
use SjorsO\Sup\Bluray\Sections\PaletteSection;
use SjorsO\Sup\Bluray\Sections\TimeSection;
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
            case "\x16":
                $section = new TimeSection($filePath, $stream->position());
                break;
            case "\x17":
                $section = new FrameSection($filePath, $stream->position());
                break;
            case "\x14":
                $section = new PaletteSection($filePath, $stream->position());
                break;
            case "\x15":
                $section = new BitmapSection($filePath, $stream->position());
                break;
            case "\x80":
                $section = new EndSection($filePath, $stream->position());
                break;
            default:
                throw new Exception("Unknown section identifier ({$sectionIdentifier})");
        }

        $stream->skip($section->getLength());

        return $section;
    }
}
