<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

class SpriteNext4Bit extends GraphicsNext
{
    public string $datatypeName = 'Next Sprite 4-bit';

    public int $tileWidth = 16;
    public int $tileHeight = 16;

    public function ReadAttribute($col, $row) : array
    {
        return $this->ReadAttribute4Bit($col, $row);
    }
}
