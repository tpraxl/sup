<?php

namespace SjorsO\Sup\Formats\Bluray;

use SjorsO\Sup\Formats\Bluray\Sections\EndSection;
use SjorsO\Sup\Formats\Sup;
use SjorsO\Sup\Formats\SupInterface;

class BluraySup extends Sup implements SupInterface
{
    protected function cue()
    {
        return BluraySupCue::class;
    }

    protected function identifier()
    {
        return 'PG';
    }

    protected function readAllCues()
    {
        $cues = [];

        while(($supCue = $this->readCue()) !== false) {
            $cues[] = $supCue;
        }

        if(count($cues) === 0) {
            return [];
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

        return $cues;
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

        if($cueHeader !== $this->identifier()) {
            return false;
        }

        return $cue;
    }
}
