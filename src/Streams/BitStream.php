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
        $this->handle = is_resource($resource) ? $resource : fopen($resource, 'rb');

        fseek($this->handle, $start);

        $this->totalBytes = $totalBytes;
    }

    protected function readNextByte()
    {
        if($this->totalBytes !== null && ++$this->bytesRead > $this->totalBytes) {
            throw new Exception('Exceeding data length (read '.$this->bytesRead.' bytes, stream at position '.$this->position().')');
        }

        $this->currentByte = unpack('C', fread($this->handle, 1))[1]; // as uint8

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
        if($length < 1) {
            throw new Exception('Need to read at least 1 bit, tried to read ' . $length);
        }

        $value = 0;

        for($i = 0; $i < $length; $i++) {
            $value = ($value << 1) + $this->bit();
        }

        return $value;
    }

    /**
     * Set pointer to the end of the current byte so the next bit read will come from the next byte.
     */
    public function skipToNextByte()
    {
        $this->currentBytePosition = 8;
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
