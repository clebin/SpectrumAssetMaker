<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class MapPaths extends TileLayer
{
    const DIRECTION_UP = 8;
    const DIRECTION_DOWN = 4;
    const DIRECTION_LEFT = 2;
    const DIRECTION_RIGHT = 1;

    public $spaceTiles = [35, 23, 24, 39, 40, 43, 62, 63, 78, 79, 81, 82, 83, 84, 97, 98, 99, 100];
    public $ladderTiles = [7, 8];
    public $slopeTiles = [];
    public $compression = false;
    public $addDimensions = true;
    public $width;
    public $height;
    public $tilemap = false;
    public $tileset = false;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->tileset = $config['tileset_obj'];
        $this->width = intval($config['width']);
        $this->height = intval($config['height']);
        $this->addDimensions = $config['add-dimensions'];
        $this->compression = $config['compression'];
    }

    public function GetData()
    {
        $row = 0;
        $col = 0;
        $moves = 0x0;
        $data = [];

        echo 'Calculating paths for ' . $this->GetName() . '.' . CR;

        for ($row = 0; $row < $this->height; $row++) {
            for ($col = 0; $col < $this->width; $col++) {

                $moves = 0;

                // up
                if ($row > 0  && $col < $this->width - 1) {

                    $tileUp1 = $this->GetTile($row - 1, $col);
                    $tileUp2 = $this->GetTile($row - 1, $col + 1);

                    if (
                        $tileUp1->isLadder() === true &&
                        $tileUp2->isLadder() === true
                    ) {
                        $moves += self::DIRECTION_UP;
                    }
                }

                // down
                if ($row < $this->height - 2 && $col < $this->width - 1) {

                    $tileDown1 = $this->GetTile($row + 2, $col);
                    $tileDown2 = $this->GetTile($row + 2, $col + 1);

                    if (
                        $tileDown1->isLadder() === true &&
                        $tileDown2->isLadder() === true
                    ) {
                        $moves += self::DIRECTION_DOWN;
                    }
                }

                // left
                if ($col > 0 && $row < $this->height - 1) {

                    $tileLeft1 = $this->GetTile($row, $col - 1);
                    $tileLeft2 = $this->GetTile($row + 1, $col - 1);

                    if (
                        $tileLeft1->isSolid() === false &&
                        $tileLeft2->isSolid() === false
                    ) {
                        $moves += self::DIRECTION_LEFT;
                    }
                }

                // right
                if ($col < $this->width - 2 && $row < $this->height - 1) {

                    $tileRight1 = $this->GetTile($row, $col + 2);
                    $tileRight2 = $this->GetTile($row + 1, $col + 2);

                    if (
                        $tileRight1->isSolid() === false &&
                        $tileRight2->isSolid() === false
                    ) {
                        $moves += self::DIRECTION_RIGHT;
                    }
                }

                // add to data
                $data[] = $moves;
            }
        }

        // compression
        if ($this->compression === 'rle') {

            if ($this->codeFormat == 'asm') {
                $addLength = true;
            } else {
                $addLength = false;
            }

            $data = App::CompressArrayRLE(
                $this->codeName,
                $data,
                $addLength,
            );
        }

        // dimensions
        if ($this->addDimensions === true) {
            array_unshift($data, $this->height, $this->width);
        }

        return $data;
    }

    public function GetTile($row, $col)
    {
        if ($this->tileset !== false) {
            return $this->tileset->GetTile($this->GetTileNum($row, $col));
        }

        echo 'Error: No tileset associated with map paths.' . CR;

        return false;
    }

    public function GetTileNum($row, $col)
    {
        return $this->data[($row * $this->width) + $col];
    }
}
