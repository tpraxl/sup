<?php

namespace SjorsO\Sup\Formats\Dvd;

use Exception;
use SjorsO\Sup\Streams\Stream;
use SjorsO\Sup\SupInterface;

class DvdSup implements SupInterface
{
    protected $filePath;

    /** @var DvdSupCue[]  */
    protected $cues = [];

    /** @var Stream */
    protected $stream;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;

        $this->stream = new Stream($this->filePath);

        $cues = [];

        while(($cueHeader = $this->stream->read(2)) === 'SP') {
            $this->stream->rewind(2);

            $cue = new DvdSupCue($this->stream, $this->filePath);

            $cues[] = $cue;
        }

        usort($cues, function(DvdSupCue $a, DvdSupCue $b) {
            return $a->getStartTime() <=> $b->getStartTime();
        });

        for($cueIndex = 0; $cueIndex < count($cues); $cueIndex++) {
            $cues[$cueIndex]->setCueIndex($cueIndex);
        }

        $this->cues = $cues;
    }

    public function extractImages($outputDirectory = './', $fileNameTemplate = 'frame-%d.png')
    {
        if(strpos($fileNameTemplate, '%d') === false) {
            throw new Exception('File name needs to contain a %d');
        }

        $extractedFilePaths = [];

        foreach($this->cues as $cue) {
            $fileName = str_replace('%d', str_pad($cue->getCueIndex(), 5, '0', STR_PAD_LEFT), $fileNameTemplate);

            $extractedFilePaths[] = $cue->extractImage($outputDirectory, $fileName);
        }

        return $extractedFilePaths;
    }

    public function getCues()
    {
        return $this->cues;
    }

    public function getCueManifest()
    {
        $manifest = [];

        foreach($this->cues as $cue) {
            $manifest[] = [
                'index' => $cue->getCueIndex(),
                'startTime' => $cue->getStartTime(),
                'endTime' => $cue->getEndTime(),
            ];
        }

        return $manifest;
    }
}