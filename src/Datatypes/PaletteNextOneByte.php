<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class PaletteNextOneByte extends PaletteNext
{
    public string $datatypeName = 'Next Palette One Byte';
    public bool $addArrayLength = false;
    
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
            $blue = intval($rgb['blue'] / 85);

            $value = $red << 5 | $green << 2 | $blue;

            // print_r($rgb);
            $this->data[] = $value;
        }
        
        return true;
    }
}