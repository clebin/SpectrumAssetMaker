<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;
use \ClebinGames\SpectrumAssetMaker\Attribute;

/*
From: BASin documentation. https://documentation.help/BASin/format_scr.html

The .scr file format

A .scr (screen) file is a memory dump of the Spectrum's display file, or screen memory. This file format is supported by many emulators.
The display file starts at address 16384 and is 6912 bytes long. The first 6144 bytes represent the actual screen bitmap (which pixels are set and which are not), 
and the remaining 768 bytes represent the attributes, or colour information, which can be imagined as a transparent overlay.

The layout of the display file is somewhat unusual. It is split into vertical thirds, each comprising eight rows of characters, and each character is stored in 
eight (non-sequential!) bytes. The attribute data is stored in a more straightforward way, "reading" the character cells from the top left to the bottom right.

This simple program illustrates the layout of the screen display by filling it with consecutive bytes:

10 FOR f=0 TO 6911 
20 POKE f+16384, 255
30 NEXT f
*/

class Screen extends Datatype
{
    public string $datatypeName = 'Screen';
    protected $image = false;

    public string $binaryFileExtension = 'scr';
    
    public string $extension = App::FILE_EXTENSION_PNG;
    public array $attributes = [];
    public array $attributeData = [];
    public array $pixelData = [];
    protected bool $addArrayLength = false;
    protected bool $addToAssetsLst = false;

    public function __construct($config)
    {
        parent::__construct($config);

        // set input file
        if ($this->inputFilepath !== false) {
            $this->isValid = $this->ReadFile($this->inputFilepath);
        }
    }

    /**
     * Read a full-screen PNG or GIF file
     */
    public function ReadFile($filename)
    {
        if (!file_exists($filename)) {
            $this->AddError('Graphics file (' . $filename . ') not found');
            return false;
        }

        // read image file
        $this->extension = substr($filename, -3);

        if ($this->extension == App::FILE_EXTENSION_PNG) {
            $this->image = imagecreatefrompng($filename);
        } else if ($this->extension == App::FILE_EXTENSION_PNG) {
            $this->image = imagecreatefromgif($filename);
        } else {
            $this->AddError('Filetype (' . $this->extension . ') not supported');
            return false;
        }

        // divide width and height into 8x8 pixel attributes      
        $dimensions = getimagesize($filename);

        if ($dimensions[0] != 256 || $dimensions[1] != 192) {
            $this->AddError('Screen has incorrect dimensions (not 256 x 192)');
            return false;
        }

        $this->SetAttributes();

        $this->SetPixelData();

        return true;
    }

    /**
     * Sets attrribute information
     */
    private function SetAttributes()
    {
        // loop through rows of atttributes
        for ($row = 0; $row < 24; $row++) {

            // loop through columns of atttributes
            for ($col = 0; $col < 32; $col++) {
                $this->SetAttribute($col, $row);
            }
        }
    }
    /**
     * Read an individual attribute (or tile)
     */
    private function SetAttribute($col, $row)
    {
        // starting values for x & y
        $startx = $col * 8;
        $starty = $row * 8;

        $attribute = [];

        // initialise counts for different colours
        $attrColours = [];
        $bright = false;

        // y
        for ($y = $starty; $y < $starty + 8; $y++) {

            $datarow = [];

            // x
            for ($x = $startx; $x < $startx + 8; $x++) {

                $rgb = imagecolorat($this->image, $x, $y);

                // get rgb values
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $colour = $this->GetColourIndex($r, $g, $b);

                // set BRIGHT
                if ($this->GetBrightForColour($r, $g, $b) === true) {
                    $bright = true;
                }

                if (isset($attrColours[$colour])) {
                    $attrColours['' . $colour]++;
                } else {
                    $attrColours['' . $colour] = 1;
                }
            }
        }

        // sort in descending order
        arsort($attrColours);

        $count = 0;
        foreach ($attrColours as $key => $val) {
            if ($count == 0) {
                $paper = $key;
            } else if ($count == 1) {
                $ink = $key;
            }
            $count++;
        }
        if ($count == 1) {
            $ink = 7;
        }

        $this->attributes[] = new Attribute($paper, $ink, $bright);
    }

    /**
     * Return attribute object at row,col
     */
    private function GetAttributeForRowCol($row, $col)
    {
        return $this->attributes[($row * 32) + $col];
    }

    /**
     * Correct colour to remove minor variations (ie. take any off-white as off-white)
     */
    private function GetCorrectedColour($col)
    {
        if ($col < 50)
            return 0;
        else if ($col < 240)
            return 192;
        else
            return 255;
    }

    /**
     * Use rgb values to determine whether colour is BRIGHT or not
     */
    private function GetBrightForColour($r, $g, $b)
    {
        if ($r == 255 || $g == 255 || $b == 255)
            return true;
        else
            return false;
    }

    /**
     * Return the colour index (0-7) according to the rgb values
     */
    private function GetColourIndex($r, $g, $b)
    {
        // correct minor variations in colour
        $r = $this->GetCorrectedColour($r);
        $g = $this->GetCorrectedColour($g);
        $b = $this->GetCorrectedColour($b);

        // black
        if ($r == 0 && $g == 0 && $b == 0)
            return 0;

        // blue
        if ($r == 0 && $g == 0 && $b > 0)
            return 1;

        // red
        if ($r > 0 && $g == 0 && $b == 0)
            return 2;

        // magenta
        if ($r > 0 && $g == 0 && $b > 0)
            return 3;

        // green
        if ($r == 0 && $g > 0 && $b == 0)
            return 4;

        // cyan
        if ($r == 0 && $g > 0 && $b > 0)
            return 5;

        // yellow
        if ($r > 0 && $g > 0 && $b == 0)
            return 6;

        // white
        if ($r > 0 && $g > 0 && $b > 0)
            return 7;
    }


    /** Set array pixel data for the screen (on or off) */
    private function SetPixelData()
    {
        $pixelData = [];

        // loop - thirds of the screen
        for ($thirds = 0; $thirds < 3; $thirds++) {

            // loop - 8 pixel rows in each character
            for ($pixRow = 0; $pixRow < 8; $pixRow++) {

                // loop - 8 character rows in each third
                for ($charRow = 0; $charRow < 8; $charRow++) {

                    // get row
                    $row = ($thirds * 8) + $charRow;

                    // get y
                    $y = ($thirds * 64) + ($charRow * 8) + $pixRow;

                    // loop - 32 columns in a row
                    for ($col = 0; $col < 32; $col++) {

                        // get new attribute every 8 pixels
                        $attr = $this->GetAttributeForRowCol($row, $col);
                        $byte = '';

                        // loop - 8 bits per column
                        for ($pix = 0; $pix < 8; $pix++) {

                            // get x
                            $x = ($col * 8) + $pix;

                            // get pixel rgb

                            $rgb = imagecolorat($this->image, $x, $y);
                            $r = ($rgb >> 16) & 0xFF;
                            $g = ($rgb >> 8) & 0xFF;
                            $b = $rgb & 0xFF;

                            // check if rgb matches paper or ink
                            $colourIndex = $this->GetColourIndex($r, $g, $b);

                            if ($colourIndex == $attr->GetPaper()) {
                                $byte .= '0';
                            } else {
                                $byte .= '1';
                            }
                        }
                        $pixel = bindec($byte);

                        $this->pixelData[] = $pixel;
                    }
                }
            }
        }
    }

    public function GetData() : array
    {
        $this->attributeData = [];

        foreach ($this->attributes as $attr)
            $this->attributeData[] = $attr->GetValue();

        $this->data = array_merge($this->pixelData, $this->attributeData);

        return $this->data;
    }

    /**
     * Return output filename only
     */
    public function GetOutputFilename(int $bank = 0) : string
    {
        return $this->outputFilename . '.scr';
    }

    public function WriteFile() : void
    {
        $this->WriteBinaryFile($this->data, $this->GetOutputFilepath());
    }
}
