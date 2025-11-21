<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class FontNext extends GraphicsNext
{
    public string $datatypeName = 'Next Font';

    public string $binaryFileExtension = 'spr';
    
    public string $binaryFormat = App::BINARY_FORMAT_1BIT;

    public int $tileWidth = 16;
    public int $tileHeight = 8;

    public function __construct($config)
    {
        parent::__construct($config);
    }
}
