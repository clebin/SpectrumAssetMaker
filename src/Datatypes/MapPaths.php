<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class MapPaths extends TileLayer
{
    public string $datatypeName = 'Map Paths';

    const int DIRECTION_UP = 8;
    const int DIRECTION_DOWN = 4;
    const int DIRECTION_LEFT = 2;
    const int DIRECTION_RIGHT = 1;

    const string MAP_STYLE_OVERHEAD = 'overhead';
    const string MAP_STYLE_PLATFORM = 'platform';

    public array $slopeTiles = [];
    public string|false $compression = App::COMPRESSION_NONE;
    public bool $addDimensions = true;

    public string $codeFormat = App::FORMAT_ASM;

    public $tilemap = false;
    public $tileset = false;

    // number of characters wide a path needs to be
    public int $pathWidth = 2;
    public int $pathHeight = 2;

    public string $mapStyle = self::MAP_STYLE_PLATFORM;
    public array $mapStyles = [
        self::MAP_STYLE_PLATFORM,
        self::MAP_STYLE_OVERHEAD
    ];

    public function __construct($config)
    {
        parent::__construct($config);

        $this->compression = false;
        $this->tileset = $config['tileset_obj'];
        $this->width = intval($config['width']);
        $this->height = intval($config['height']);
        $this->addDimensions = $config['add-dimensions'];
        $this->compression = $config['compression'];

        // path width
        if (isset($config['path-width'])) {
            $this->pathWidth = intval($config['path-width']);
        }

        // path height
        if (isset($config['path-height'])) {
            $this->pathHeight = intval($config['path-height']);
        } else {
            $this->pathHeight = $this->pathWidth;
        }

        // map style - overhead or platform
        if (isset($config['path-map-style']) && in_array($config['path-map-style'], $this->mapStyles)) {
            $this->mapStyle = $config['path-map-style'];
        }
    }

    public function GetData() : array
    {
        $row = 0;
        $col = 0;
        $moves = 0x0;
        $data = [];

        if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
            App::OutputMessage($this->datatypeName, $this->name, 'Calculating paths.');
        }

        for ($row = 0; $row < $this->height; $row++) {
            for ($col = 0; $col < $this->width; $col++) {

                $moves = 0;

                // up
                if ($row > 0  && $col < $this->width - 1) {

                    $canMove = true;
                    for ($i = 0; $i < $this->pathWidth; $i++) {

                        $tile = $this->GetTile($row - 1, $col + $i);

                        if (
                            ($this->mapStyle == self::MAP_STYLE_PLATFORM && $tile->IsLadder() === false) ||
                            ($this->mapStyle == self::MAP_STYLE_OVERHEAD && $tile->IsSolid() === true)
                        ) {
                            $canMove = false;
                        }
                    }

                    if ($canMove === true) {
                        $moves += self::DIRECTION_UP;
                    }
                }

                // down
                if ($row < $this->height - 2 && $col < $this->width - 1) {

                    $canMove = true;
                    for ($i = 0; $i < $this->pathWidth; $i++) {

                        $tile = $this->GetTile($row + 2, $col + $i);

                        if (($this->mapStyle == self::MAP_STYLE_PLATFORM && $tile->isLadder() === false) ||
                            ($this->mapStyle == self::MAP_STYLE_OVERHEAD && $tile->isSolid() === true)
                        ) {
                            $canMove = false;
                        }
                    }

                    if ($canMove === true) {
                        $moves += self::DIRECTION_DOWN;
                    }
                }

                // left
                if ($col > 0 && $row < $this->height - 1) {

                    $canMove = true;
                    for ($i = 0; $i < $this->pathHeight; $i++) {

                        $tile = $this->GetTile($row + $i, $col - 1);

                        if ($tile->isSolid() === true)
                            $canMove = false;
                    }

                    if ($canMove === true) {
                        $moves += self::DIRECTION_LEFT;
                    }
                }

                // right
                if ($col < $this->width - 2 && $row < $this->height - 1) {

                    $canMove = true;
                    for ($i = 0; $i < $this->pathHeight; $i++) {

                        $tile = $this->GetTile($row + $i, $col + 2);

                        if ($tile->isSolid() === true)
                            $canMove = false;
                    }

                    if ($canMove === true) {
                        $moves += self::DIRECTION_RIGHT;
                    }
                }

                // add to data
                $data[] = $moves;
            }
        }

        // compression
        if ($this->compression === App::COMPRESSION_RLE) {

            if ($this->codeFormat == 'asm') {
                $addLength = true;
            } else {
                $addLength = false;
            }

            $data = App::CompressArrayRLE(
                $this->name,
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
