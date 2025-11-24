<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class GraphicsClassic extends Graphics
{
    public const DATATYPE_NAME = 'Graphics';

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