<?php

namespace SjorsO\Sup\Streams;

use RuntimeException;

class Stream
{
    protected $handle;

    protected $maximumPosition = null;

    public function __construct($filePath, $position = 0, $maximumPosition = null)
    {
        $this->handle = fopen($filePath, 'rb');

        $this->maximumPosition = $maximumPosition;

        $this->skip($position);
    }

    public function byte()
    {
        return $this->read(1);
    }

    public function bytes($length)
    {
        return str_split($this->read($length));
    }

    public function uint8()
    {
        return unpack('C', $this->read(1))[1];
    }

    public function uint16()
    {
        return unpack('n', $this->read(2))[1];
    }

    public function uint16le()
    {
        return unpack('v', $this->read(2))[1];
    }

    public function uint32()
    {
        return unpack('N', $this->read(4))[1];
    }

    public function uint32le()
    {
        return (int) round(unpack('V', $this->read(4))[1] / 90);
    }

    public function skip($length)
    {
        if ($length !== 0) {
            $this->assertMaximumPosition($length);

            fseek($this->handle, $length, SEEK_CUR);
        }

        return $this;
    }

    public function read($length)
    {
        $this->assertMaximumPosition($length);

        return fread($this->handle, $length);
    }

    public function seek($position)
    {
        fseek($this->handle, $position, SEEK_SET);

        return $this;
    }

    public function rewind($length)
    {
        fseek($this->handle, -$length, SEEK_CUR);

        return $this;
    }

    public function position()
    {
        return ftell($this->handle);
    }

    protected function assertMaximumPosition($readLength)
    {
        if ($this->maximumPosition === null) {
            return;
        }

        if ($this->position() + $readLength > $this->maximumPosition) {
            throw new RuntimeException("Reading $readLength would exceed maximum position ($this->maximumPosition)");
        }
    }
}
