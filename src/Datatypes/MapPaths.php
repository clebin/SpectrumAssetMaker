<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class MapPaths extends TileLayer
{
    const DIRECTION_UP = 8;
    const DIRECTION_DOWN = 4;
    const DIRECTION_LEFT = 2;
    const DIRECTION_RIGHT = 1;

    public $spaceTiles = [35, 23, 24, 39, 40, 62, 63, 78, 79];
    public $ladderTiles = [7, 8];
    public $slopeTiles = [];

    public function GetData()
    {
        $row = 0;
        $col = 0;
        $moves = 0x0;
        $data = [];

        echo 'Calculating paths for ' . $this->GetName() . CR;

        for ($row = 0; $row < 10; $row++) {
            for ($col = 0; $col < 10; $col++) {

                $moves = 0;

                // up
                if (
                    $row > 0 &&
                    $this->isLadder($row - 1, $col) === true &&
                    $this->isLadder($row - 1, $col + 1) === true
                ) {
                    $moves += self::DIRECTION_UP;
                }

                // down
                if (
                    $row < $this->height - 1 &&
                    $this->isLadder($row + 2, $col) === true &&
                    $this->isLadder($row + 2, $col + 1) === true
                ) {
                    $moves += self::DIRECTION_DOWN;
                }

                // left
                if (
                    $col > 0 &&
                    $this->isSpace($row, $col - 1) === true &&
                    $this->isSpace($row + 1, $col - 1) === true
                ) {
                    $moves += self::DIRECTION_LEFT;
                }

                // right
                if (
                    $col < $this->width - 1 &&
                    $this->isSpace($row, $col + 2) === true &&
                    $this->isSpace($row + 1, $col + 2) === true
                ) {
                    $moves += self::DIRECTION_RIGHT;
                }

                // add to data
                $data[] = $moves;
            }
        }
        return $data;
    }

    public function isLadder($row, $col)
    {
        $tileNum = $this->getTile($row, $col);

        if (in_array($tileNum, $this->ladderTiles)) {
            return true;
        }
        return false;
    }
    public function isSpace($row, $col)
    {
        $tileNum = $this->getTile($row, $col);

        if (
            in_array($tileNum, $this->spaceTiles) ||
            in_array($tileNum, $this->ladderTiles)
        ) {
            return true;
        }
        return false;
    }

    public function GetTile($row, $col)
    {
        return $this->data[($row * $this->width) + $col];
    }
}
