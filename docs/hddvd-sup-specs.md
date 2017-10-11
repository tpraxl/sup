# HD-DVD Specs
This file describes the specs of a HD-DVD sup file. Some small (but important) details are not described here, you can look them up in the source code.
 
A HD-DVD sup is made up out of sections. Each section starts with 20 bytes of header data:
```C#
struct SectionHeader
{
     char[2] identifier = {'S', 'P'};
     int32 startTimeMilliseconds; //little endian
     int32 unknown1;
     int16 unknown2;
     int32 firstSequencePosition;  // actualPosition = sectionStartPosition + firstSequencePosition  + 1
     int32 secondSequencePosition; // actualPosition = sectionStartPosition + secondSequencePosition + 10   
}
```

Both sequence positions in the header point to a control sequence:
```C#
struct ControlSequence
{
    int16 endTime;
    int32 nextSequencePosition;
    MetadataBlock[] metadataBlocks;
}
```

There are 6 different metadata blocks:

| Identifier | Length      |                             |
|------------|-------------|-----------------------------|
| 0x01       | 0           | start time                  |
| 0x02       | 0           | end time                    |
| 0x83       | 768         | colors                      |
| 0x84       | 256         | color alphas                |
| 0x85       | 6           | bitmap size and coordinates |
| 0x86       | 8           | positions of bitmap data    |
| 0xff       | 0           | end of block                |


## Metadata blocks
Information about each metadata block is listed below

#### 0x01 - Start time
No purpose

#### 0x02 - End time
If this block is present in a control sequence, then the endTime value from the control sequence header is used to calculate the cue duration:

`cueDurationInMilliseconds = ((timeCorrection << 10) + 1023) / 90;`

#### 0x83 - Colors
This block contains the color palette of the subtitle in 256 entries of 3 bytes each in YCbCr format.

```
y = read_one_byte() - 16;
cb = read_one_byte() - 128;
cr = read_one_byte() - 128;
 
r = min(0, max(255, round(1.1644 * y + 1.596 * cr)));
g = min(0, max(255, round(1.1644 * y - 0.813 * cr - 0.391 * cb)));
b = min(0, max(255, round(1.1644 * y + 2.018 * cb)));
```

#### 0x84 - Color alphas
Contains the alpha values for each color from the colors section. 0x00 is opaque, 0xff is transparent.

#### 0x85 - Bitmap size and coordinates
Contains 6 bytes, it should be read as four 12 bit integers.
```
int12 x1;
int12 x2; // bitmap width = x2 - x1 + 1
int12 y1;
int12 y2; // bitmap height = y2 - y1 + 1
```

#### 0x86 - Positions of bitmap data
Contains two pointers to the bitmap data of the subtitle. The first one points to the start of the odd lines, the second to the start of the even lines.
```
int32 startOddLineData;  // actualPosition = sectionStart + startOddLineData  + 10
int32 startEvenLineData; // actualPosition = sectionStart + startEvenLineData + 10
```

#### 0xff - End of block
Indicates the end of this control sequence block.

-------------------

## Decoding the bitmap

The bitmap is stored as a RLE compressed interlaced 256 color bitmap. The RLE works on a bit level so you have to read the byte stream one bit at a time.

Following is some pseudo-code that will decode a line of subtitle bitmap. Remember that the bitmap is interlaced, so you need to have two streams, one for the odd lines and one for the even lines.

```C#
while (y < bitmap_height)
{	
    x = 0;
    
    while (x < bitmap_width)
    {
        rle_type = read_1_bit();
        
        color_type = read_1_bit();
        
        colorIndex = color_type ? read_8_bits() : read_2_bits();
        
        if (rle_type == 1)
        {
            rle_size = read_1_bit();
            
            if (rle_size == 1)
            {
                number_of_pixels = read_7_bits()  + 9;                
                
                if (number_of_pixels == 9)
                {
                    number_of_pixels = bitmap_width - x;
                }                
            }
            else
            {
                number_of_pixels = read_3_bits()  + 2;                
            }
        }
        else
        {
            number_of_pixels = 1;
        }
        
        append_pixels(colors[colorIndex], number_of_pixels);
        
        x = x + number_of_pixels;
    }
    
    y++;
    // when at the end of a line, the stream needs to use the next byte, regardless of the current bit it is on
}
```
