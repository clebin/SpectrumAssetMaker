<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class GraphicsClassic extends Graphics
{
    public static string $datatypeName = 'Graphics';
    public string $paperColour = App::COLOUR_BLACK;

    public function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * Read an individual attribute (or tile)
     */
    public function ReadAttribute($col, $row) : array
    {
        return $this->ReadAttribute1Bit($col, $row);
    }
}