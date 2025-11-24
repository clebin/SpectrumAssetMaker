<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

abstract class GraphicsNext extends Graphics
{
    public const DATATYPE_NAME = 'Next Graphics';

    public string $binaryFileExtension = 'nxt';
    public bool $addArrayLength = false;

    public string $binaryFormat = App::BINARY_FORMAT_8BIT;
    protected string $codeFormat = App::FORMAT_BINARY;

    public function __construct($config)
    {
        parent::__construct($config);
    }

    public function ReadPixelsInRows() : array
    {
        $data = [];
        $count = 0;

        // loop through rows
        for ($y = 0; $y < $this->numRows; $y++) {

            // loop through columns
            for ($x = 0; $x < $this->numColumns; $x++) {

                // add pixel
                $data[] = $this->ReadPixel($x, $y);
                $count++;
            }
        }
        
        return $data;
    }

    public function ReadPixelsInColumns() : array
    {
        $data = [];
        $count = 0;

        // loop through columns
        for ($x = 0; $x < $this->numColumns; $x++) {

            // loop through rows
            for ($y = 0; $y < $this->numRows; $y++) {

                // add pixel
                $data[] = $this->ReadPixel($x, $y);
                $count++;
            }
        }

        return $data;
    }
}
