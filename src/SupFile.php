<?php

namespace SjorsO\Sup;

use Exception;
use SjorsO\Sup\Formats\Bluray\BluraySup;
use SjorsO\Sup\Formats\Dvd\DvdSup;
use SjorsO\Sup\Formats\Hddvd\HddvdSup;
use SjorsO\Sup\Formats\SupInterface;

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
    public static function getFormat($filePath)
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
            case 'PG': return BluraySup::class;
            case 'SP':
                return ($header[8] === "\x00" && $header[9] === "\x00") ? HddvdSup::class : DvdSup::class;
        }

        return false;
    }

    /**
     * @param $filePath
     * @return SupInterface|bool
     * @throws Exception
     */
    public static function open($filePath)
    {
        $sup = self::getFormat($filePath);

        return ($sup === false) ? false : new $sup($filePath);
    }
}
