<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class BitmapNext extends GraphicsNext
{
    public string $datatypeName = 'Next Bitmap';

    public bool $addArrayLength = false;

    public string $binaryFileExtension = 'nxi';
    protected string $codeFormat = App::FORMAT_BINARY;
    public string $bitmapFormat = App::NEXT_BITMAP_FORMAT_ROWS;
    
    public int $tileWidth = 1;
    public int $tileHeight = 1;

    public function __construct($config)
    {
        parent::__construct($config);

        // set screen format
        if( isset($config['bitmap-format']) && 
            $config['bitmap-format'] == App::NEXT_BITMAP_FORMAT_COLUMNS ) {
            
            $this->bitmapFormat = App::NEXT_BITMAP_FORMAT_COLUMNS;
        }
        // take default or what's set in setting json
        else {    
            $this->bitmapFormat = App::$nextScreenFormat;
        }
    }

    public function ReadImage() : array
    {
        if( $this->bitmapFormat == App::NEXT_BITMAP_FORMAT_COLUMNS) {
            return $this->ReadPixelsInColumns();
        }
        return $this->ReadPixelsInRows();
    }
}
