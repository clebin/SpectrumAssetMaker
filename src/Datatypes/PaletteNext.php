<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

abstract class PaletteNext extends Datatype
{
    public string $datatypeName = 'Next Palette';

    public int $numColours = 256;
    public array $colours = [];
    protected \GdImage $image;

    public function __construct($config)
    {
        parent::__construct($config);

        if( isset($config['num-colours']) && intval($config['num-colours']) > 0) {
            $this->numColours = intval($config['num-colours']);
        }

        $this->isValid = $this->ReadFile($this->inputFilepath);
    }

    abstract function ReadFile(string $filename): bool;
}