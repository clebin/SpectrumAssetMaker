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
}
