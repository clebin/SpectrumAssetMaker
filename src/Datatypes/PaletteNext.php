<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class PaletteNext extends Datatype
{
    public string $datatypeName = 'Next Palette';
    public string $binaryFileExtension = 'pal';

    public string $binaryFormat = App::BINARY_FORMAT_ONE_BYTE;

    public bool $addArrayLength = false;
    public int $numColours = 256;
    public array $colours = [];
    protected \GdImage $image;

    public function __construct($config)
    {
        parent::__construct($config);

        if( isset($config['num-colours']) && 
            intval($config['num-colours']) > 0 &&
            intval($config['num-colours']) <= 256 ) {
            $this->numColours = intval($config['num-colours']);
        }

        if( isset($config['binary-format']) && $config['binary-format'] == App::BINARY_FORMAT_TWO_BYTE)
        {
            $this->binaryFormat = App::BINARY_FORMAT_TWO_BYTE;
        }

        $this->isValid = $this->ReadFile($this->inputFilepath);
    }

    public function ReadFile(string $filename): bool
    {
        if (!file_exists($filename)) {
            App::AddError('File (' . $filename . ') not found');
            return false;
        }

        // read image file
        $this->image = $this->GetImageFromFile($filename);

        $this->numColours = imagecolorstotal($this->image);

        if( $this->binaryFormat == App::BINARY_FORMAT_ONE_BYTE) {

            $this->ReadPaletteOneByte();
        } else {
            $this->ReadPaletteTWoBytes();
        }

        App::OutputMessage($this->datatypeName, $this->name, 'Read '.$this->numColours.' colours from '.$filename.' ('.$this->binaryFormat.' format)');

        return true;
    }

    public function ReadPaletteOneByte() : void
    {
        for($i=0;$i<$this->numColours;$i++)
        {
            $rgb = imagecolorsforindex($this->image, $i);

            // get cut down values
            $red = intval($rgb['red'] / 32);
            $green = intval($rgb['green'] / 32);
            $blue = intval($rgb['blue'] / 85);

            $value = $red << 5 | $green << 2 | $blue;

            // print_r($rgb);
            $this->data[] = $value;
        }
    }

    public function ReadPaletteTwoBytes() : void
    {
        for($i=0;$i<$this->numColours;$i++)
        {
            $rgb = imagecolorsforindex($this->image, $i);

            // get cut down values
            $red = intval($rgb['red'] / 32);
            $green = intval($rgb['green'] / 32);
            $blue = intval($rgb['blue'] / 32);

            // first byte RRRGGBB
            $value = $red << 5 | $green << 2 | ($blue >> 1);

            // second byte 0000000B
            $value2 = $blue & 1;
            
            // $bin_val = str_pad(decbin($value), 8, '0', STR_PAD_LEFT);
            // $bin_val2 = str_pad(decbin($value2), 8, '0', STR_PAD_LEFT);
            // echo 'Bin: '.$bin_val.' / '. $bin_val2.' ('.$red.' | '.$green.' | '.$blue.' = '. $value. ')'.CR;

            // add two bytes to data
            $this->data[] = $value;
            $this->data[] = $value2;
        }
    }
}