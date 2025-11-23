<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class SpriteNext extends GraphicsNext
{
    public static string $datatypeName = 'Next Sprite';

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

        // tile width
        if( isset($config['tile-width']) && intval($config['tile-width']) > 0) {
            $this->tileWidth = intval($config['tile-width']);
        }

        // tile height
        if( isset($config['tile-height']) && intval($config['tile-height']) > 0) {
            $this->tileHeight = intval($config['tile-height']);
        }
    }
}
