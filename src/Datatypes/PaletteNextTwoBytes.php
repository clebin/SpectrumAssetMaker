<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class PaletteNextTwoBytes extends PaletteNext
{
    public string $datatypeName = 'Next Palette Two Bytes';

    public function ReadFile(string $filename): bool
    {
        if (!file_exists($filename)) {
            App::AddError('File (' . $filename . ') not found');
            return false;
        }

        // read image file
        $this->image = $this->GetImageFromFile($filename);

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
        
        return true;
    }
}
