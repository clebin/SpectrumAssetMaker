<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class SpriteNext extends GraphicsNext
{
    public string $datatypeName = 'Next Sprite';

    public string $binaryFileExtension = 'spr';
    
    public string $binaryFormat = App::BINARY_FORMAT_4BIT;

    public int $tileWidth = 16;
    public int $tileHeight = 16;

    public function __construct($config)
    {
        parent::__construct($config);

        // tile layer format
        if( isset($config['binary-format']) && $config['binary-format'] == App::BINARY_FORMAT_8BIT) {
            $this->binaryFormat = App::BINARY_FORMAT_8BIT;
        }

    }

    public function ReadAttribute($col, $row) : array
    {
        if( $this->binaryFormat == App::BINARY_FORMAT_8BIT) {
            return $this->ReadAttribute8Bit($col, $row);
        }
        return $this->ReadAttribute4Bit($col, $row); 
    }
}
