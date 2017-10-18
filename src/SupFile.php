<?php

namespace SjorsO\Sup;

use Exception;
use SjorsO\Sup\Bluray\BluraySup;
use SjorsO\Sup\Formats\Dvd\DvdSup;
use SjorsO\Sup\Hddvd\HddvdSup;

class SupFile
{
    private function __construct()
    {
    }

    /**
     * @param $filePath
     * @return SupInterface|bool
     * @throws Exception
     */
    public static function open($filePath)
    {
        if(! file_exists($filePath)) {
            throw new Exception('File does not exist');
        }

        if(filesize($filePath) < 12) {
            return false;
        }

        $handle = fopen($filePath, 'rb');

        $identifier = fread($handle, 2);

        $header = fread($handle, 10);

        fclose($handle);

        switch($identifier)
        {
            case 'PG': return new BluraySup($filePath);
            case 'SP':
                return ($header[8] === "\x00" && $header[9] === "\x00") ? new HddvdSup($filePath) : new DvdSup($filePath);
        }

        return false;
    }
}
