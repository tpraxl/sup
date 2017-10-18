<?php

namespace SjorsO\Sup\Formats\Hddvd;

use SjorsO\Sup\Formats\Sup;
use SjorsO\Sup\Formats\SupInterface;

class HddvdSup extends Sup implements SupInterface
{
    protected function cue()
    {
        return HddvdSupCue::class;
    }

    protected function identifier()
    {
        return 'SP';
    }
}
