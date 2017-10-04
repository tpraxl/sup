<?php

namespace SjorsO\Sup;

use Exception;
use SjorsO\Sup\Bluray\Sup;

class SupFile
{
    private function __construct()
    {
    }

    public static function open($filePath)
    {
        if(! file_exists($filePath)) {
            throw new Exception('File does not exist');
        }

        if(filesize($filePath) < 2) {
            return false;
        }

        $handle = fopen($filePath, 'rb');

        $identifier = fread($handle, 2);

        fclose($handle);

        switch($identifier)
        {
            case 'PG': return new Sup($filePath);
            case 'PS': throw new Exception('Not implemented');
        }

        return false;
    }
}
