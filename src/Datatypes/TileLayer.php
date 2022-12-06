<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

/**
 * Class representing a tilemap
 */
class TileLayer extends Datatype
{
    public $num = 0;
    public $width = false;
    public $height = false;
    public $tilemap;
    protected $addArrayLength = false;

    public function __construct($tilemap, $num, $layer)
    {
        $this->tilemap = $tilemap;
        $this->num = $num;
        $this->data = $this->ReadLayer($layer);
    }

    /**
     * Read a Tiled tilemap layer
     */
    public function ReadLayer($layer)
    {
        $data = [];

        // map dimensions
        $this->width = $layer['width'];
        $this->height = $layer['height'];

        // data
        foreach ($layer['data'] as $tileNum) {

            $tileNum = intval($tileNum) - 1;
            $data[] = $tileNum;
        }

        // return a Screen object
        return $data;
    }

    /**
     * Get array of tile numbers for specified screen
     */
    public function GetTileNums()
    {

        $tileNums = [];
        foreach ($this->data as $attr) {
            $tileNums[] = $attr->tileNum;
        }
        return $tileNums;
    }

    /**
     * Get data
     */
    public function GetData()
    {
        // compression
        if (App::$compression === 'rle') {

            if (App::GetFormat() == 'asm') {
                $add_length = true;
            } else {
                $add_length = false;
            }

            $data = App::CompressArrayRLE(
                $this->codeName,
                $this->data,
                $add_length,
            );
        } else {
            $data = $this->data;
        }

        // dimensions
        if (App::GetAddDimensions() === true) {
            array_unshift($data, $this->height, $this->width);
        }

        return $data;
    }
}
