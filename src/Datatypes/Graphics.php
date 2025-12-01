<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

abstract class Graphics extends Datatype
{
    public const DATATYPE_NAME = 'Graphics';
    
    public int $numColumns = 0;
    public int $numRows = 0;
    public int $numTiles = 0;

    public bool $addArrayLength = false;

    public int $tileWidth = 8;
    public int $tileHeight = 8;

    protected \GdImage $image;
    
    public function __construct($config)
    {
        parent::__construct($config);

        // binary format
        if( isset($config['binary-format']) && in_array($config['binary-format'], App::$binaryFormatsSupported)) {
            $this->binaryFormat = $config['binary-format'];
        }

        // set input file
        if ($this->inputFilepath === false) {

            $this->isValid = false;
            $this->AddError('No input specified');
            return;
        }
    }

    public function Process() : void
    {
        $this->isValid = $this->ReadFile($this->inputFilepath);

        if($this->isValid === true) {
            $this->WriteFile();
        }
    }

    /**
     * Read a PNG or GIF file
     */
    public function ReadFile($filename): bool
    {
        if (!file_exists($filename)) {
            $this->AddError('File (' . $filename . ') not found');
            return false;
        }

        // read image file
        $this->extension = substr($filename, -3);

        // png
        if ($this->extension == App::FILE_EXTENSION_PNG) {
            $this->image = imagecreatefrompng($filename);
        }
        // gif
        else if ($this->extension == App::FILE_EXTENSION_GIF) {
            $this->image = imagecreatefromgif($filename);
        }
        // not supported
        else {
            $this->AddError('Filetype (' . $this->extension . ') not supported');
            return false;
        }

        // convert to true colour
        if( $this->binaryFormat == App::BINARY_FORMAT_1BIT ) {
            imagepalettetotruecolor($this->image);
        }

        // verbosity
        if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
            $this->AddMessage('Reading ' . $this->extension . ' file');
        }

        // divide width and height into 8x8 (or 16x16) pixel attributes
        $dimensions = getimagesize($filename);

        $this->numColumns = $dimensions[0] / $this->tileWidth;
        $this->numRows = $dimensions[1] / $this->tileHeight;
        $this->numTiles = $this->numColumns * $this->numRows;

        // output dimensions
        $this->AddMessage('Image size: '.$dimensions[0] . 'x' . $dimensions[1] . 'px');

        $this->data = $this->ReadImage();

        return true;
    }

    /**
     * Read a single pixel by x and y
     */
    public function ReadPixel(int $x, int $y) : int
    {
        $value = imagecolorat($this->image, $x, $y);
        
        if( $value < 0 || $value > 255) {
            $value = 1;
        }

        return $value;
    }

    /**
     * Abstract function for reading an attribute
     */
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
                
                $value = bindec($value);
                // $bin_val = str_pad(decbin($value), 8, '0', STR_PAD_LEFT);
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
        $startx = $col * 8;
        $starty = $row * 8;

        $attribute = [];

        // rows
        for ($y = $starty; $y < $starty + 8; $y++) {

            $datarow = [];

            // cols
            for ($x = $startx; $x < $startx + 8; $x++) {

                // $rgb = $this->ReadPixel($x, $y);
                $pixel = imagecolorat($this->image, $x, $y);

                // transparent counts as paper, or black or white depending on setting
                if ($this->ColourIsPaper($pixel) === true) {
                    $pixel = 0;
                }
                // anything else is ink
                else {
                    $pixel = 1;
                }

                // add pixel value to this row
                $datarow[] = $pixel;
            }

            $datarow = bindec(implode('', $datarow));

            echo $datarow.' ';
            // add row of data
            $attribute[] = $datarow;
        }
        
        return $attribute;
        
    }

    /**
     * Read pixel data
     */
    public function ReadImage() : array
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

    /**
     * Get raw tile data for a numbered tile
     */
    public function GetTileData($num)
    {
        if (isset($this->data[$num])) {
            return $this->data[$num];
        } else {
            return false;
        }
    }
}
