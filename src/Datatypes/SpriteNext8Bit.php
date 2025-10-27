<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

class SpriteNext8Bit extends GraphicsNext
{
    public int $tileWidth = 16;
    public int $tileHeight = 16;

    public function ReadAttribute($col, $row) : array
    {
        return $this->ReadAttribute8Bit($col, $row);
    }
}
