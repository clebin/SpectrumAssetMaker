<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

/**
 * Class representing a tilemap
 */
class TileLayer extends Datatype
{
    public string $datatypeName = 'Tile Layer';

    public int $num = 0;
    public int|false $width = false;
    public int|false $height = false;
    public string|false $compression = false;
    public string $codeFormat = App::FORMAT_ASM;
    public bool $addDimensions = true;
    protected bool $addArrayLength = false;

    public $tilemap;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->tilemap = $config['tilemap'];
        $this->num = $config['num'];
        $this->width = intval($config['width']);
        $this->height = intval($config['height']);
        $this->addDimensions = $config['add-dimensions'];
        $this->compression = $config['compression'];

        $this->data = $this->ReadLayer($config['data']);
        
        $this->codeSection = $config['section'];
    }

    /**
     * Read a Tiled tilemap layer
     */
    public function ReadLayer($layer)
    {
        $data = [];
        // data
        foreach ($layer as $tileNum) {

            $tileNum = intval($tileNum) - 1;

            if ($tileNum < 0 || $tileNum > 255) {
                echo 'Error: Probably invalid tile number (' . $tileNum . ')' . CR;
                $tileNum = 0;
            }

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
    public function GetData() : array
    {
        // compression
        if ($this->compression === App::COMPRESSION_RLE) {

            if ($this->codeFormat == 'asm') {
                $add_length = true;
            } else {
                $add_length = false;
            }

            $data = App::CompressArrayRLE(
                $this->name,
                $this->data,
                $add_length,
            );
        } else {
            $data = $this->data;
        }

        // dimensions
        if ($this->addDimensions === true) {
            array_unshift($data, $this->height, $this->width);
        }

        return $data;
    }
}
