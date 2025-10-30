<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class TileLayerNext extends TileLayer
{
    // tiled attribute handling
    public const TILED_XFLIP_MASK = 2147483648;
    public const TILED_YFLIP_MASK = 1073741824;
    public const TILED_ROTATE_MASK = 134217728;
    public const TILED_ATTRIBUTE_RSHIFT = 28;

    public string $binaryFileExtension = 'nxm';

    public $binaryFormat = App::BINARY_FORMAT_TWO_BYTE;

    public function __construct($config)
    {
        parent::__construct($config);

        if( isset($config['binary-format']) && $config['binary-fomat'] == App::BINARY_FORMAT_ONE_BYTE) {
            $this->binaryFormat = App::BINARY_FORMAT_ONE_BYTE;
        }
    }

    /**
     * Read a Tiled tilemap layer
     */
    public function ReadLayer($layer)
    {
        if( $this->binaryFormat == App::BINARY_FORMAT_ONE_BYTE) {
            return $this->ReadLayerOneByte($layer);
        }

        return $this->ReadLayerTwoByte($layer);
    }
    
    /**
     * Read a Tiled tilemap layer
     */
    public function ReadLayerOneByte($layer)
    {
        $data = [];
        // data
        foreach ($layer as $tileNum) {

            $tileNum = intval($tileNum) - 1;
            $tileNum = $tileNum & self::TILED_TILE_NUM_MASK;

            $data[] = $tileNum;
        }

        // return a Screen object
        return $data;
    }

    /**
     * Read a Tiled tilemap layer
     */
    public function ReadLayerTwoByte($layer)
    {
        $data = [];
        // data
        foreach ($layer as $tileNum) {

            $tileNum = intval($tileNum) - 1;

            // palette offset
            $palette_offset = 0;

            // attributes
            $xflip = ($tileNum & self::TILED_XFLIP_MASK) >> self::TILED_ATTRIBUTE_RSHIFT;
            $yflip = ($tileNum & self::TILED_YFLIP_MASK) >> self::TILED_ATTRIBUTE_RSHIFT;
            $rotate = ($tileNum & self::TILED_ROTATE_MASK) >> self::TILED_ATTRIBUTE_RSHIFT;

            $attributes = $palette_offset | $xflip | $yflip | $rotate;

            // tile num
            $tileNum = $tileNum & self::TILED_TILE_NUM_MASK;

            // tile number byte
            $data[] = $tileNum;

            // attributes byte
            $data[] = $attributes;

        }

        // return data
        return $data;
    }
}
