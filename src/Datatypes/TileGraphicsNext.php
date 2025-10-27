<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

class TileGraphicsNext extends GraphicsNext
{
    public string $datatypeName = 'Next Graphics';

    public int $tileWidth = 8;
    public int $tileHeight = 8;

    public function ReadAttribute($col, $row) : array
    {
        return $this->ReadAttribute4Bit($col, $row);
    }
}