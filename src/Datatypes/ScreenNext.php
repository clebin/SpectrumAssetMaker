<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;
use \ClebinGames\SpectrumAssetMaker\Datatypes\BitmapNext;

class ScreenNext extends BitmapNext
{
    public string $datatypeName = 'Next Layer 2 Screen';
    public bool $addPalette = false;

    public function __construct($config)
    {
        if( isset($config['add-palette'])) {
            $this->addPalette = true;
        }

        parent::__construct($config);
    }

    public function ReadFile($filename): bool
    {
        $result = parent::ReadFile($filename);

        if( $result === false ) {
            return false;
        }

        // append palette to file - 2 bytes per palette entry
        if( $this->addPalette === true) {
            $palette = new PaletteNext($this->config);
            $this->data = array_merge($this->data, $palette->GetData());
        }

        return true;
    }

    // public function ReadAttribute($col, $row) : array
    // {
    //     return $this->ReadAttribute8Bit($col, $row);
    // }
}
