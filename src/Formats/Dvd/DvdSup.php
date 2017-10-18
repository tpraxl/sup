<?php

namespace SjorsO\Sup\Formats\Dvd;

use SjorsO\Sup\Formats\SupInterface;
use SjorsO\Sup\Formats\Sup;

class DvdSup extends Sup implements SupInterface
{
    protected function cue()
    {
        return DvdSupCue::class;
    }

    protected function identifier()
    {
        return 'SP';
    }
}
