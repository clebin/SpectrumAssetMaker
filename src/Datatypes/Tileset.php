<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;
use \ClebinGames\SpectrumAssetMaker\Tile;

/**
 * Class representing a tileset with functions for reading and exporting
 */
class Tileset extends Datatype
{
    protected $tilesetIsSet = false;
    protected $addProperties = false;
    protected $replaceFlashWithSolid = false;

    // array of tiles
    protected $tiles = [];
    public $large_tileset = false;

    public function __construct($config)
    {
        parent::__construct($config);

        // set sprite image
        if (isset($config['tileset'])) {
            $filename = $config['tileset'];
        } else {
            $this->isValid = false;
            return;
        }

        // solid
        if (isset($config['replace-flash-with-solid']) && $config['replace-flash-with-solid'] == 'true') {
            $this->replaceFlashWithSolid = true;
        }

        // properties
        if (isset($config['add-tileset-properties']) && $config['add-tileset-properties'] == 'true') {
            $this->addProperties = true;
        }

        $this->isValid = $this->ReadFile($filename);
    }

    /**
     * Set name and filename
     */
    public function SetName($name)
    {
        $this->name = $name;
        $this->codeName = App::GetConvertedCodeName($name, $this->codeFormat);
        $this->filename = App::GetConvertedFilename($name . '-properties');
        $this->defineName = App::GetConvertedConstantName($name . '-len');
    }

    /**
     * Read the tileset JSON file
     */
    public function ReadFile($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }

        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        $count = 0;
        foreach ($data['tiles'] as $tile) {

            $id = intval($tile['id']);

            // save to tiles array using id as key
            $this->tiles[] = new Tile($id, $tile['properties'], $this->replaceFlashWithSolid);

            $count++;
        }

        // need to represent tile numbers with 16 bits
        if (sizeof($this->tiles) > 256) {
            $this->large_tileset = true;
        }

        App::OutputMessage('Tileset', $this->name, 'Added ' . $count . ' tiles');

        $this->tilesetIsSet = true;

        return true;
    }

    /**
     * Return tile object for index
     */
    public function GetTile($index)
    {
        // tile found
        if (isset($this->tiles[$index]))
            return $this->tiles[$index];

        // tile not found
        echo 'Tile #' . $index . ' not found. Using tile 0.' . CR;
        return $this->tiles[0];
    }

    public function TilesetIsSet()
    {
        return $this->tilesetIsSet;
    }

    /**
     * Get number of tiles in tileset
     */
    public function GetNumTiles()
    {
        return sizeof($this->tiles);
    }

    /**
     * Return whether a tile with a particular id exists in the tileset
     */
    public function TileExists($id)
    {
        return isset($this->tiles[$id]);
    }

    /**
     * Return tileset in assembly format
     */
    public function GetCodeAsm()
    {
        $str = $this->GetHeaderAsm();

        // tile info
        $colours = [];
        $properties = [];

        foreach ($this->tiles as $tile) {
            $colours[] = $tile->GetColoursByte();
            $properties[] = $tile->GetPropertiesByte();
        }

        // colours
        $str .= App::GetAsmArray(
            $this->codeName . 'Colours',
            $colours,
            2
        ) . CR;

        // properties
        if ($this->addProperties === true) {
            $str .= App::GetAsmArray(
                $this->codeName . 'Properties',
                $properties,
                2
            ) . CR;
        }

        return $str;
    }

    /**
     * Return C array of tile colours and properties
     */
    public function GetCodeC()
    {
        $str = '';

        $str .= '#define ' . strtoupper($this->defineName) . ' ' . sizeof($this->tiles) . CR . CR;

        // tile info
        $colours = [];
        $properties = [];

        foreach ($this->tiles as $tile) {
            $colours[] = $tile->GetColoursByte();

            $props = $tile->GetPropertiesByte();

            // if ($props != '00000000') {
            //     $this->addProperties = true;
            // }

            $properties[] = $props;
        }

        // colours
        $str .= App::GetCArray(
            $this->codeName . 'Colours',
            $colours,
            2,
            $this->large_tileset
        ) . CR;

        // properties array
        if ($this->addProperties === true) {
            $str .= App::GetCArray(
                $this->codeName . 'Properties',
                $properties,
                2,
                $this->large_tileset
            ) . CR;
        }

        return $str;
    }
}
