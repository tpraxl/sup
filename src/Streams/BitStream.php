<?php

namespace SjorsO\Sup\Streams;

use RunTimeException;

class BitStream
{
    protected $handle;

    protected $endPosition;

    protected $bytesRead = 0;

    protected $currentByte;

    protected $currentBytePosition = 8;

    public function __construct($resource, $startPosition = 0, $endPosition = null)
    {
        $this->handle = is_resource($resource) ? $resource : fopen($resource, 'rb');

        fseek($this->handle, $startPosition);

        $this->endPosition = $endPosition;
    }

    protected function readNextByte()
    {
        if ($this->endPosition !== null && ++$this->bytesRead > $this->endPosition) {
            throw new RunTimeException('Exceeding data length (read '.$this->bytesRead.' bytes, stream at position '.$this->position().')');
        }

        $this->currentByte = unpack('C', fread($this->handle, 1))[1]; // as uint8

        $this->currentBytePosition = 0;
    }

    public function bit()
    {
        if ($this->currentBytePosition === 8) {
            $this->readNextByte();
        }

        return ($this->currentByte & (0b10000000 >> $this->currentBytePosition++)) ? 1 : 0;
    }

    public function bool()
    {
        return (bool) $this->bit();
    }

    public function bits($length)
    {
        if ($length < 1) {
            throw new RunTimeException('Need to read at least 1 bit, tried to read '.$length);
        }

        $value = 0;

        for ($i = 0; $i < $length; $i++) {
            $value = ($value << 1) + $this->bit();
        }

        return $value;
    }

    /**
     * Set pointer to the end of the current byte so the next bit read will come
     * from the next byte.
     *
     * @return $this
     */
    public function skipToNextByte()
    {
        $this->currentBytePosition = 8;

        return $this;
    }

    /**
     * Get the current position of the stream in the resource.
     *
     * @return int
     */
    public function position()
    {
        return ftell($this->handle);
    }

    /**
     * Get the total size in bytes of the resource being read.
     *
     * @return int
     */
    public function size()
    {
        return fstat($this->handle)['size'];
    }

    public static function fromData($data)
    {
        $stream = fopen('php://memory', 'rb+');

        fwrite($stream, $data);

        rewind($stream);

        return new static($stream, 0, strlen($data));
    }
}
