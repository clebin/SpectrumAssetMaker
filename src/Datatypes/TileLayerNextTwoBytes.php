<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class TileLayerNextTwoBytes extends TileLayer
{
    /**
     * Read a Tiled tilemap layer
     */
    public function ReadLayer($layer)
    {
        $data = [];
        // data
        foreach ($layer as $tileNum) {

            $tileNum = intval($tileNum) - 1;

            // palette offset
            $palette_offset = 0;

            // attributes
            $xflip = ($tileNum & self::TILED_XFLIP_MASK) >> 27;
            $yflip = ($tileNum & self::TILED_YFLIP_MASK) >> 27;
            $rotate = ($tileNum & self::TILED_ROTATE_MASK) >> 27;

            $attributes = $palette_offset | $xflip | $yflip | $rotate;

            // tile num
            $tileNum = $tileNum & self::TILED_TILE_NUM_MASK;

            // first byte
            $data[] = $attributes;

            // second byte is tile number
            $data[] = $tileNum;
        }

        // return a Screen object
        return $data;
    }
}
