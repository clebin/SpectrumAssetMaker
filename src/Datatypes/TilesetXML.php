<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;
use \ClebinGames\SpectrumAssetMaker\Tile;

/**
 * Class representing a tileset with functions for reading and exporting
 */
class TilesetXML extends Tileset
{
    /**
     * Set name and filename
     */
    public function SetName($name)
    {
        $this->name = $name;
        $this->codeName = App::GetConvertedCodeName($name);
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

        $xml = file_get_contents($filename);
        $data = App::objectToArray(simplexml_load_string($xml));

        $count = 0;
        foreach ($data['tile'] as $tile) {

            $id = intval($tile['@attributes']['id']);

            $properties = [];

            foreach ($tile['properties']['property'] as $prop) {
                $properties[] = $prop['@attributes'];
            }

            // save to tiles array using id as key
            $this->tiles[] = new Tile($id, $properties);

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
}
