<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

abstract class GraphicsNext extends Graphics
{
    public string $datatypeName = 'Next Graphics';

    public string $binaryFileExtension = 'nxt';
    public bool $addArrayLength = false;

    protected string $codeFormat = App::FORMAT_BINARY;

    public function __construct($config)
    {
        parent::__construct($config);
    }

    public function ReadAttributes() : array
    {
        $data = [];

        // loop through rows of atttributes
        for ($row = 0; $row < $this->numRows; $row++) {

            // loop through columns of atttributes
            for ($col = 0; $col < $this->numColumns; $col++) {
                $attribute = $this->ReadAttribute($col, $row);

                $data = array_merge($data, $attribute);
            }
        }

        return $data;
    }

    abstract function ReadAttribute($col, $row) : array;

    /**
     * Read an individual attribute (or tile) - using 8 bits per pixel
     */
    public function ReadAttribute8Bit($col, $row) : array
    {
        // starting values for x & y
        $startx = $col * $this->tileWidth;
        $starty = $row * $this->tileHeight;

        $attribute = [];

        // rows
        for ($y = $starty; $y < $starty + $this->tileHeight; $y++) {
            
            // cols
            for ($x = $startx; $x < $startx + $this->tileWidth; $x++) {

                $value = imagecolorat($this->image, $x, $y);
                
                if( $value < 0 || $value >= 256) {
                    $value = 0;
                }

                // $bin_val = str_pad(decbin($value), 8, '0', STR_PAD_LEFT);
                // echo '('.$x.','.$y.') '.$value.' ('.$bin_val.')'.CR;

                // add row of data
                $attribute[] = $value;
            }
        }

        return $attribute;
    }

    /**
     * Read an individual attribute (or tile) - using 4 bits per pixel
     */
    public function ReadAttribute4Bit($col, $row) : array
    {
        // starting values for x & y
        $startx = $col * $this->tileWidth;
        $starty = $row * $this->tileHeight;

        $attribute = [];

        // rows
        for ($y = $starty; $y < $starty + $this->tileHeight; $y++) {
            
            // cols
            for ($x = $startx; $x < $startx + $this->tileWidth; $x++) {

                $pixelColour1 = imagecolorat($this->image, $x, $y);
                
                // echo $pixelColour1.' ';
                if( $pixelColour1 < 0 || $pixelColour1 >= 16) {
                    $pixelColour1 = 0;
                }

                $pixelColour1 = $pixelColour1 << 4;
                

                // next pixel
                $x++;

                $pixelColour2 = imagecolorat($this->image, $x, $y);
                
                // echo $pixelColour2.' ';
                if( $pixelColour2 < 0 || $pixelColour2 >= 16) {
                    $pixelColour2 = 0;
                }

                // combine the two into one byte
                $value = $pixelColour1 | $pixelColour2;
                
                $bin_val = str_pad(decbin($value), 8, '0', STR_PAD_LEFT);
                // echo '('.$x.','.$y.') '.$pixelColour1.' | '.$pixelColour2.' = '.$value.' ('.$bin_val.')'.CR;

                // add row of data
                $attribute[] = $value;
            }
        }

        return $attribute;
    }
}
