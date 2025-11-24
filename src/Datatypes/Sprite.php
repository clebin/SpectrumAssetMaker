<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class Sprite extends Datatype
{
    public static string $datatypeName = 'Sprite';

    private $spriteImage = false;
    private $maskImage = false;

    private array $spriteData = [];
    private array $maskData = [];

    public int $width = 0;
    public int $height = 0;
    public int $numColumns = 0;

    public string $binaryFormat = App::BINARY_FORMAT_1BIT;

    public string $spriteExtension = App::FILE_EXTENSION_GIF;
    public string $maskExtension = App::FILE_EXTENSION_GIF;

    public function __construct($config)
    {
        parent::__construct($config);

        // set sprite image
        if ($this->inputFilepath === false) {
            $this->AddError('No input specified');
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
        } else {
            $maskFile = false;
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

        $this->AddMessage('Created ' . $this->numColumns . ' columns (' . $this->width . ' x ' . $this->height . 'px)');
   
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
            $this->AddError('File "' . $filename . '" not found');
            return false;
        }

        // read image file
        $extension = substr($filename, -3);

        // file extension
        if ($mask === true) {
            $this->maskExtension = $extension;
        } else {
            $this->extension = $extension;
            $this->spriteExtension = $extension;
        }

        // get the image
        if ($extension == App::FILE_EXTENSION_PNG) {
            $image = imagecreatefrompng($filename);
        } else if ($extension == App::FILE_EXTENSION_GIF) {
            $image = imagecreatefromgif($filename);
        } else {
            $this->AddError('Filetype (' . $extension . ') not supported');
            return false;
        }

        // convert to true colour
        if( $this->binaryFormat == App::BINARY_FORMAT_1BIT ) {
            imagepalettetotruecolor($image);
        }

        return $image;
    }

    public function GetImageData($image, $mask = false)
    {
        $data = [];

        // loop through columns
        for ($col = 0; $col < $this->numColumns; $col++) {
            $data[] = $this->GetAttributeData($image, $col, $mask);
        }
        return $data;
    }

    /**
     * Read an individual attribute (or tile)
     */
    private function GetAttributeData($image, $col, $mask = false)
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
                if ($this->ColourIsPaper($rgb) === true) {
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
    public function GetCodeAsm() : string
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
