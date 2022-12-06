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

    // array of tiles
    protected $tiles = [];
    public $large_tileset = false;

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
            $this->tiles[] = new Tile($id, $tile['properties']);

            $count++;
        }

        // need to represent tile numbers with 16 bits
        if (sizeof($this->tiles) > 256) {
            $this->large_tileset = true;
        }

        echo 'Tileset: added ' . $count . ' tiles.' . CR;

        $this->tilesetIsSet = true;

        return true;
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

    public function GetBinariesLst()
    {
        // return $this->GetOutputBaseFilename();
    }

    /**
     * Return tileset in assembly format
     */
    public function GetAsm()
    {
        $str = 'SECTION ' . App::GetCodeSection() . CR;

        $str .= CR;
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
        if (App::ReplaceFlashWithSolid() === false) {
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
    public function GetC()
    {
        $str = '';

        $str .= '#define ' . strtoupper($this->defineName) . ' ' . sizeof($this->tiles) . CR . CR;

        // tile info
        $colours = [];
        $properties = [];
        foreach ($this->tiles as $tile) {
            $colours[] = $tile->GetColoursByte();
            $properties[] = $tile->GetPropertiesByte();
        }

        // colours
        $str .= App::GetCArray(
            $this->codeName . 'Colours',
            $colours,
            2,
            $this->large_tileset
        ) . CR;

        // properties array
        if (App::ReplaceFlashWithSolid() === false) {
            $str .= App::GetCArray(
                $this->codeName . 'Properties',
                $properties,
                2,
                $this->large_tileset
            ) . CR;
        }

        return $str;
    }

    public function Process($filename)
    {
        $success = $this->ReadFile($filename);

        if ($success === true) {
            $this->WriteFile();
        }
    }
}
