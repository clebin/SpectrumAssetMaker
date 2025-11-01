<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

abstract class Graphics extends Datatype
{
    public string $datatypeName = 'Graphics';
    
    public int $numColumns = 0;
    public int $numRows = 0;
    public int $numTiles = 0;
    public string $extension = App::FILE_EXTENSION_PNG;

    public int $tileWidth = 8;
    public int $tileHeight = 8;

    protected \GdImage $image;
    
    public function __construct($config)
    {
        parent::__construct($config);
     
        // set input file
        if ($this->inputFilepath === false) {

            $this->isValid = false;
            App::AddError($this->datatypeName . ': No input specified for "' . $this->name . '"');
            return;
        }
    }

    public function Process() : void
    {
        $this->isValid = $this->ReadFile($this->inputFilepath);

        if($this->isValid === true) {
            $this->WriteFile();
        }
    }

    /**
     * Read a PNG or GIF file
     */
    public function ReadFile($filename): bool
    {
        if (!file_exists($filename)) {
            App::AddError('Graphics file (' . $filename . ') not found');
            return false;
        }

        // read image file
        $this->extension = substr($filename, -3);

        if ($this->extension == App::FILE_EXTENSION_PNG) {
            $this->image = imagecreatefrompng($filename);
        } else if ($this->extension == App::FILE_EXTENSION_GIF) {
            $this->image = imagecreatefromgif($filename);
        } else {
            App::AddError('Filetype (' . $this->extension . ') not supported');
            return false;
        }

        if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
            App::OutputMessage($this->datatypeName, $this->name, 'Reading ' . $this->extension . ' file');
        }

        // divide width and height into 8x8 (or 16x16) pixel attributes      
        $dimensions = getimagesize($filename);

        $this->numColumns = $dimensions[0] / $this->tileWidth;
        $this->numRows = $dimensions[1] / $this->tileHeight;
        $this->numTiles = $this->numColumns * $this->numRows;

        // output dimensions
        App::OutputMessage($this->datatypeName, $this->name, 'Image size: '.$dimensions[0] . 'x' . $dimensions[1] . 'px');
        App::OutputMessage($this->datatypeName, $this->name, 'Tiles: '.$this->numColumns.'x'.$this->numRows.'='.$this->numTiles);

        $this->data = $this->ReadAttributes();

        return true;
    }

    /**
     * Read pixel data
     */
    abstract function ReadAttributes() : array;

    /**
     * Return pixel data for image
     */
    abstract function ReadAttribute($col, $row) : array;

    /**
     * Get raw tile data for a numbered tile
     */
    public function GetTileData($num)
    {
        if (isset($this->data[$num])) {
            return $this->data[$num];
        } else {
            return false;
        }
    }
}
