<?php

namespace SjorsO\Sup;

use Exception;
use SjorsO\Sup\Bluray\BluraySup;
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

        $handle = fopen($filePath, 'rb');

        $identifier = fread($handle, 2);

        fclose($handle);

        switch($identifier)
        {
            case 'PG': return new BluraySup($filePath);
            case 'SP': return new HddvdSup($filePath);
        }

        return false;
    }
}
