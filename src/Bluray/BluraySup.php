<?php

namespace SjorsO\Sup\Bluray;

use Exception;
use SjorsO\Sup\Bluray\Sections\EndSection;
use SjorsO\Sup\Streams\Stream;
use SjorsO\Sup\SupInterface;

class BluraySup implements SupInterface
{
    protected $filePath;

    /** @var BluraySupCue[]  */
    protected $cues = [];

    /** @var Stream */
    protected $stream;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;

        $this->stream = new Stream($this->filePath);

        /** @var BluraySupCue[] $cues */
        $cues = [];

        while(($supCue = $this->readCue()) !== false) {
            $cues[] = $supCue;
        }

        if(count($cues) === 0) {
            return;
        }

        for($i = 1; $i < count($cues); $i++) {
            $previousCue = $cues[$i - 1];

            $previousCue->setEndTime($cues[$i]->getStartTime());
        }

        $lastCue = $cues[count($cues) - 1];

        $lastCue->setEndTime($lastCue->getStartTime() + 3000);

        // Cues without an image are only there to indicate the previous cue should end
        $cues = array_filter($cues, function(BluraySupCue $cue) {
            return $cue->containsImage();
        });

        usort($cues, function(BluraySupCue $a, BluraySupCue $b) {
           return $a->getStartTime() <=> $b->getStartTime();
        });

        for($cueIndex = 0; $cueIndex < count($cues); $cueIndex++) {
            $cues[$cueIndex]->setCueIndex($cueIndex);
        }

        $this->cues = $cues;
    }

    protected function readCue()
    {
        $cue = new BluraySupCue();

        while(($cueHeader = $this->stream->read(2)) === 'PG') {

            $section = BluraySection::get($this->stream, $this->filePath);

            $cue->addSection($section);

            if($section instanceof EndSection) {
                break;
            }
        }

       //$section->exportDataSection(__DIR__ . '/' . $this->stream->position());
       //exit;

        if($cueHeader !== 'PG') {
            return false;
        }

        return $cue;
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
