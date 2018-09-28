# Sup
Work in progress

## Installation
Required extensions:
- [gd](http://php.net/manual/en/book.image.php) - for creating images from sup cues

Installation:
```bash
composer require sjorso/sup
```

## Usage
```php
$sup = SupFile::open('/path/to/file.sup');
 
$imageFilePaths = $sup->extractImages('/path/to/output/directory/');
```

Extracted images usually look something like this:

![example japanese cue](https://i.imgur.com/pC3cAIG.png)

![example arabic cue](https://i.imgur.com/3Qyvqnk.png)

## Sources
The bluray sup format was ported from the [exar.ch suprip source code](https://github.com/peterdk/SupRip/blob/master/SupRip/SubtitleFile.cs)

The hd-dvd sup format was made based on [these specs](https://github.com/peterdk/SupRip/blob/master/Hddvd%20Sup.txt)

The dvd sup format was ported from the [subtitle creator source code](https://sourceforge.net/p/subtitlecreator/svn/HEAD/tree/trunk/SUP.cs)

## License

This project is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
