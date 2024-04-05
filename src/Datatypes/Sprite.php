<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class Sprite extends Datatype
{
    private $spriteImage = false;
    private $maskImage = false;

    private $spriteData = [];
    private $maskData = [];

    public $width = 0;
    public $height = 0;
    public $numColumns = 0;
    public $spriteExtension = 'gif';
    public $maskExtension = 'gif';
    public $paperColour = App::COLOUR_BLACK;

    public function __construct($config)
    {
        parent::__construct($config);

        // set sprite image
        if ($this->inputFilepath === false) {
            $this->isValid = false;
            return;
        }

        // paper colour
        if (isset($config['paper-colour']) && in_array($config['paper-colour'], App::$coloursSupported)) {
            $this->paperColour = $config['paper-colour'];
        }

        // set mask image
        if (isset($config['mask'])) {
            $maskFile = $config['mask'];
        }

        // input file
        if ($this->inputFilepath !== false) {
            $this->isValid = $this->ReadFiles($this->inputFilepath, $maskFile);
        }
    }

    /**
     * Read a black & white PNG or GIF file
     */
    public function ReadFiles($spriteFile, $maskFile = false)
    {
        $this->spriteImage = $this->GetImage($spriteFile);

        if ($maskFile !== false) {
            $this->maskImage = $this->GetImage($maskFile, true);
        }

        if (App::DidErrorOccur() === true) {
            return false;
        }

        // set dimensions for the main sprite
        // divide width and height into 8x8 pixel attributes      
        $this->width = imagesx($this->spriteImage);
        $this->height = imagesy($this->spriteImage);
        $this->numColumns = $this->width / 8;

        if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
            echo 'Sprite:   Created sprite ' . $this->numColumns . ' columns (' . $this->width . ' x ' . $this->height . 'px)' . CR;
        }

        // get raw pixel data
        $this->spriteData = $this->GetImageData($this->spriteImage);

        if ($maskFile !== false) {
            $this->maskData = $this->GetImageData($this->maskImage, true);
        }

        return true;
    }

    public function GetImage($filename, $mask = false)
    {
        if (!file_exists($filename)) {
            App::AddError('File "' . $filename . '" not found');
            return false;
        }

        // read image file
        $extension = substr($filename, -3);

        // file extension
        if ($mask === true) {
            $this->maskExtension = $extension;
        } else {
            $this->spriteExtension = $extension;
        }

        // get the image
        if ($extension == 'png') {
            return imagecreatefrompng($filename);
        } else if ($extension == 'gif') {
            return imagecreatefromgif($filename);
        } else {
            App::AddError('Filetype (' . $extension . ') not supported');
            return false;
        }
    }

    public function GetImageData($image, $mask = false)
    {
        $data = [];

        // loop through columns
        for ($col = 0; $col < $this->numColumns; $col++) {
            $data[] = $this->GetPixelData($image, $col, $mask);
        }
        return $data;
    }

    /**
     * Read an individual attribute (or tile)
     */
    private function GetPixelData($image, $col, $mask = false)
    {
        // starting values for x
        $startx = $col * 8;

        $coldata = [];

        // file extension
        if ($mask === true) {
            $extension = $this->maskExtension;
        } else {
            $extension = $this->spriteExtension;
        }

        // rows
        for ($y = 0; $y < $this->height; $y++) {

            $linedata = [];

            // cols
            for ($x = $startx; $x < $startx + 8; $x++) {

                $rgb = imagecolorat($image, $x, $y);

                // transparent counts as paper, or black or white depending on setting
                if (App::ColourIsPaper($rgb, $this->paperColour, $extension) === true) {
                    $pixel = ($mask === true ? 1 : 0);
                }
                // anything else is ink
                else {
                    $pixel = ($mask === true ? 0 : 1);
                }

                // add pixel value to this row
                $linedata[] = $pixel;
            }

            // add row of data
            $coldata[] = $linedata;
        }

        return $coldata;
    }

    /**
     * Return sprite graphics in C format
     */
    public function GetCodeC()
    {
        $str = 'Error: C sprite export is not supported.';
        return $str;
    }

    /**
     * Return sprite graphics in assembly format
     */
    public function GetCodeAsm()
    {
        $str = $this->GetHeaderAsm();
        $str .= 'public ' . $this->codeName . CR . CR;

        // front padding
        for ($line = 0; $line < 7; $line++) {
            if ($this->maskImage !== false) {
                $str .= 'defb @11111111, @00000000' . CR;
            } else {
                $str .= 'defb @00000000' . CR;
            }
        }

        $str .= CR . '.' . $this->codeName . CR;

        for ($col = 0; $col < $this->numColumns; $col++) {

            // loop through data
            for ($line = 0; $line < sizeof($this->spriteData[$col]); $line++) {

                // mask
                if ($this->maskImage !== false) {
                    if (isset($this->maskData[$col][$line])) {
                        $val = implode('', $this->maskData[$col][$line]);
                        $str .= 'defb @' . $val;
                    } else {
                        $str .= 'defb @00000000';
                    }

                    // sprite
                    $val = implode('', $this->spriteData[$col][$line]);
                    $str .= ', @' . $val;

                    $str .= CR;
                }
                // unmasked
                else {
                    $val = implode('', $this->spriteData[$col][$line]);
                    $str .= 'defb @' . $val . CR;
                }
            }

            $str .= CR;
            // footer padding
            for ($line = 0; $line < 8; $line++) {
                if ($this->maskImage !== false) {
                    $str .= 'defb @11111111, @00000000' . CR;
                } else {
                    $str .= 'defb @00000000' . CR;
                }
            }
        }

        return $str;
    }
}
