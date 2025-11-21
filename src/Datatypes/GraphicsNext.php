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

    // abstract function for ReadImage()
    abstract public function ReadImage() : array;


    public function ReadPixelsInRows() : array
    {
        $data = [];
        $count = 0;

        // loop through rows
        for ($y = 0; $y < $this->numRows; $y++) {

            // loop through columns
            for ($x = 0; $x < $this->numColumns; $x++) {

                // add pixel
                $data[] = $this->ReadPixel($x, $y);
                $count++;
            }
        }
        
        return $data;
    }

    public function ReadPixelsInColumns() : array
    {
        $data = [];
        $count = 0;

        // loop through columns
        for ($x = 0; $x < $this->numColumns; $x++) {

            // loop through rows
            for ($y = 0; $y < $this->numRows; $y++) {

                // add pixel
                $data[] = $this->ReadPixel($x, $y);
                $count++;
            }
        }

        return $data;
    }

    public function ReadPixel(int $x, int $y) : int
    {
        $value = imagecolorat($this->image, $x, $y);
        
        if( $value < 0 || $value >= 256) {
            $value = 0;
        }

        // $bin_val = str_pad(decbin($value), 8, '0', STR_PAD_LEFT);
        // echo '('.$x.','.$y.') '.$value.' ('.$bin_val.')'.CR;
        return $value;
    }

    public function ReadAttribute($col, $row) : array
    {
        switch($this->binaryFormat) {

            case App::BINARY_FORMAT_4BIT:
                return $this->ReadAttribute4Bit($col, $row); 
                break;

            case App::BINARY_FORMAT_1BIT:
                return $this->ReadAttribute1Bit($col, $row); 
                break;

            default:
                return $this->ReadAttribute8Bit($col, $row);
                break;
        }
    }

    /**
     * Read an individual attribute (or tile) - using 8 bits per pixel
     */
    public function ReadAttribute8Bit(int $col, int $row) : array
    {
        // starting values for x & y
        $startx = $col * $this->tileWidth;
        $starty = $row * $this->tileHeight;

        $attribute = [];

        // rows
        for ($y = $starty; $y < $starty + $this->tileHeight; $y++) {
            
            // cols
            for ($x = $startx; $x < $startx + $this->tileWidth; $x++) {

                $attribute[] = $this->ReadPixel($x, $y);
            }
        }

        return $attribute;
    }

    /**
     * Read an individual attribute (or tile) - using 4 bits per pixel
     */
    public function ReadAttribute4Bit(int $col, int $row) : array
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

    /**
     * Read an attribute using 1 bit per pixel
     */
    public function ReadAttribute1Bit(int $col, int $row) : array
    {
        // starting values for x & y
        $startx = $col * $this->tileWidth;
        $starty = $row * $this->tileHeight;

        $attribute = [];

        // rows
        for ($y = $starty; $y < $starty + $this->tileHeight; $y++) {

            $value = '';

            // cols
            for ($x = $startx; $x < $startx + $this->tileWidth; $x++) {

                $pixel = $this->ReadPixel($x, $y);

                if( $pixel > 0 ) {
                    $value .= '1';
                } else {
                    $value .= '0';
                }

                if(strlen($value) == 8) {

                    $attribute[] = bindec($value);
                    $value = '';
                }
            }
        }

        return $attribute;
    }
}
