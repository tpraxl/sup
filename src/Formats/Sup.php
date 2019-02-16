<?php

namespace SjorsO\Sup\Formats;

use Exception;
use SjorsO\Sup\Streams\Stream;

abstract class Sup
{
    protected $filePath;

    protected $cues = [];

    protected $stream;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;

        $this->stream = new Stream($this->filePath);

        $cues = $this->readAllCues();

        usort($cues, function(SupCueInterface $a, SupCueInterface $b) {
            return $a->getStartTime() <=> $b->getStartTime();
        });

        for($cueIndex = 0; $cueIndex < count($cues); $cueIndex++) {
            $cues[$cueIndex]->setCueIndex($cueIndex + 1);
        }

        $this->cues = $cues;
    }

    protected function readAllCues()
    {
        $cues = [];

        while($this->stream->read(2) === $this->identifier()) {
            $this->stream->rewind(2);

            $cueClass = $this->cue();

            $cue = new $cueClass($this->stream, $this->filePath);

            $cues[] = $cue;
        }

        return $cues;
    }

    public function extractImages($outputDirectory = './', $fileNameTemplate = 'frame-[%d-%t].png')
    {
        $extractedFilePaths = [];

        foreach ($this->cueIndexes() as $index) {
            $extractedFilePaths[] = $this->extractImage($index, $outputDirectory, $fileNameTemplate);
        }

        return $extractedFilePaths;
    }

    public function extractImage($index, $outputDirectory = './', $fileNameTemplate = 'frame-[%d-%t].png')
    {
        if(strpos($fileNameTemplate, '%d') === false) {
            throw new Exception('File name needs to contain a %d');
        }

        $fileNameTemplate = str_replace('%t', str_pad(count($this->cues), 5, '0', STR_PAD_LEFT), $fileNameTemplate);

        $cue = $this->cues[$index];

        $fileName = str_replace('%d', str_pad($cue->getCueIndex(), 5, '0', STR_PAD_LEFT), $fileNameTemplate);

        return $cue->extractImage($outputDirectory, $fileName);
    }

    /**
     * @return array
     *
     * @deprecated Use "cues()" instead
     */
    public function getCues()
    {
        return $this->cues();
    }

    public function cues()
    {
        return $this->cues;
    }

    public function cueIndexes()
    {
        return array_keys($this->cues());
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

    protected abstract function cue();

    protected abstract function identifier();
}
