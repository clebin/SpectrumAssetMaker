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

    /**
     * Read a black & white PNG or GIF file
     */
    public function ReadFiles($spriteFile, $maskFile = false)
    {
        $this->spriteImage = self::GetImage($spriteFile);

        if ($maskFile !== false) {
            $this->maskImage = self::GetImage($maskFile);
        }

        if (App::DidErrorOccur() === true) {
            return false;
        }

        // set dimensions for the main sprite
        // divide width and height into 8x8 pixel attributes      
        $this->width = imagesx($this->spriteImage);
        $this->height = imagesy($this->spriteImage);
        $this->numColumns = $this->width / 8;

        echo 'Reading sprite: ' . $this->numColumns . ' columns (' . $this->width . ' x ' . $this->height . 'px)';

        // get raw pixel data
        $this->spriteData = self::GetImageData($this->spriteImage);

        if ($maskFile !== false) {
            $this->maskData = self::GetImageData($this->maskImage, true);
        }

        return true;
    }

    public function GetImage($filename)
    {
        if (!file_exists($filename)) {
            App::AddError('File "' . $filename . '" not found');
            return false;
        }

        // read image file
        $extension = substr($filename, -3);

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
            $data[] = self::GetPixelData($image, $col, $mask);
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

        // rows
        for ($line = 0; $line < $this->height; $line++) {

            $linedata = [];

            // cols
            for ($x = $startx; $x < $startx + 8; $x++) {

                $rgb = imagecolorat($image, $x, $line);

                // get rgb values
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // pure black counts as ink
                if ($r == 0 && $g == 0 && $b == 0) {

                    $pixel = ($mask === true ? 1 : 0);
                }
                // anything else is paper
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
    public function GetC()
    {
        $str = 'Error: C sprite export is not supported.';
        return $str;
    }

    /**
     * Return sprite graphics in assembly format
     */
    public function GetAsm()
    {
        $str = 'SECTION ' . App::GetCodeSection() . CR;

        $str .= 'PUBLIC _' . $this->codeName . CR . CR;

        // front padding
        for ($line = 0; $line < 7; $line++) {
            if ($this->maskImage !== false) {
                $str .= 'defb @11111111, @00000000' . CR;
            } else {
                $str .= 'defb @00000000' . CR;
            }
        }

        $str .= CR . '._' . $this->codeName . CR;

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

    public function Process($spriteFilename, $maskFilename)
    {
        $success = self::ReadFiles($spriteFilename, $maskFilename);

        if ($success === true) {
            $this->WriteFile();
            // file_put_contents(App::GetOutputFilename('sprite'), self::GetCode());
        }
    }
}
