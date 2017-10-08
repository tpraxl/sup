<?php

namespace SjorsO\Sup\Streams;

use Exception;

class BitStream
{
    protected $handle;

    protected $totalBytes;

    protected $bytesRead = 0;

    protected $currentByte;

    protected $currentBytePosition = 8;

    public function __construct($resource, $start = 0, $totalBytes = null)
    {
        if (is_resource($resource)) {
            $this->handle = $resource;
        } else {
            $this->handle = fopen($resource, 'rb');
        }

        fseek($this->handle, $start);

        $this->totalBytes = $totalBytes;
    }

    protected function readNextByte()
    {
        if($this->totalBytes !== null && ++$this->bytesRead > $this->totalBytes) {
            throw new Exception('Exceeding data length (read '.$this->bytesRead.' @ '.$this->position().')');
        }

        // as uint8
        $this->currentByte = unpack('C', fread($this->handle, 1))[1];

        $this->currentBytePosition = 0;
    }

    public function bit()
    {
        if($this->currentBytePosition === 8) {
            $this->readNextByte();
        }

        return ($this->currentByte & (0b10000000 >> $this->currentBytePosition++)) ? 1 : 0;
    }

    public function bool()
    {
        return (bool)$this->bit();
    }

    public function bits($length)
    {
        $value = 0;

        for($i = 0; $i < $length; $i++) {
            $value = ($value << 1) + $this->bit();
        }

        return $value;
    }

    public function position()
    {
        return ftell($this->handle);
    }

    public static function fromData($data)
    {
        $stream = fopen('php://memory', 'rb+');

        fwrite($stream, $data);

        rewind($stream);

        return new BitStream($stream, 0, strlen($data));
    }
}
