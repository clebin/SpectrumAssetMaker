<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

/**
 * Class representing a tilemap with functions for reading and exporting
 */
class TilemapNext extends Tilemap
{
    public static string $datatypeName = 'Tilemap Next';

    public string $binaryFormat = App::BINARY_FORMAT_ONE_BYTE;

    public function __construct($config)
    {
        parent::__construct($config);

        // tile layer format
        if( isset($config['binary-format']) && $config['binary-format'] == App::BINARY_FORMAT_TWO_BYTE) {
            $this->binaryFormat = App::BINARY_FORMAT_TWO_BYTE;
        }
    }


    /**
     * Read tile layer
     */
    public function ReadLayer($args)
    {
        $args['binary-format'] = $this->binaryFormat;
        
        return new TileLayerNext($args);
    }
}