<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

class TileGraphicsNext extends GraphicsNext
{
    public string $datatypeName = 'Tile Graphics Next';

    public int $tileWidth = 8;
    public int $tileHeight = 8;

    public function __construct($config)
    {
        parent::__construct($config);
    }

    public function ReadAttributes() : array
    {
        $data = [];

        // loop through rows of atttributes
        for ($row = 0; $row < $this->numRows; $row++) {

            // loop through columns of atttributes
            for ($col = 0; $col < $this->numColumns; $col++) {
                $attribute = $this->ReadAttribute($col, $row);
                $data = array_merge($data, $attribute);
            }
        }


        return $data;
    }

    public function ReadAttribute($col, $row) : array
    {
        return $this->ReadAttribute8Bit($col, $row);
    }
}