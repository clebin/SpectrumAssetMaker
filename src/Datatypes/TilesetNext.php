<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;
use \ClebinGames\SpectrumAssetMaker\Tile;

/**
 * Class representing a tileset with functions for reading and exporting
 */
class Tileset extends Tileset
{
    public string $datatypeName = 'Next Tileset';

    public static array $defaultTilePropertyDefinitions = [];
}