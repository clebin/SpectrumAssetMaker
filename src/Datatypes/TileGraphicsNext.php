<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

class TileGraphicsNext extends GraphicsNext
{
    public const DATATYPE_NAME = 'Tile Graphics Next';

    public int $tileWidth = 8;
    public int $tileHeight = 8;

    public function __construct($config)
    {
        parent::__construct($config);
    }

    public function ReadAttribute($col, $row) : array
    {
        return $this->ReadAttribute8Bit($col, $row);
    }
}