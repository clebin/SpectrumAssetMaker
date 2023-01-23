<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

/**
 * Class representing a tilemap with functions for reading and exporting
 */
class Tilemap extends Datatype
{
    // data arrays
    public $maps = [];

    public $numTileLayers = 0;
    public $numObjectMaps = 0;

    public $defineName = 'TILEMAPS_LEN';
    public $width = false;
    public $height = false;

    // allowed properties on enemies, objects, etc.
    private $object_allowed_properties = [
        'collectable',
        'deltax',
        'deltay',
        'speed',
        'numhits',
        'transient',
        'lethal',
        'endval',
        'movement'
    ];

    /**
     * Read a tilemap JSON file.
     */
    public function ReadFile($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }

        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        // read file with groups
        if (isset($data['layers'][0]['layers'])) {
            $success = $this->ReadFileWithGroups($data);
        }
        // read simple
        else {
            $success = $this->ReadFileSimple($data);
        }

        return $success;
    }

    /**
     * Read a simple file with only tilemap layers and no groups
     */
    public function ReadFileSimple($data)
    {
        return $this->ReadLayerGroup($data['layers']);
    }

    public function ReadFileWithGroups($data)
    {
        // loop through groups
        $this->numTileLayers = 0;
        $this->numObjectMaps = 0;

        foreach ($data['layers'] as $group) {
            $this->ReadLayerGroup($group['layers'], $group['name']);
        }

        return true;
    }

    public function ReadLayerGroup($group, $groupName = false)
    {
        foreach ($group as $layer) {

            $map = false;
            $paths = false;
            echo 'Reading layer "' . $layer['name'] . '" (' . $layer['type'] . ')' . CR;

            // tilemap
            if (App::GetIgnoreHiddenLayers() === true && $layer['hidden'] === true) {
                // do nothing
                $map = false;
                $paths = false;
            }
            // tile layer
            else if (
                $layer['type'] == 'tilelayer' &&
                (App::GetLayerType() == 'tilelayer' || App::GetLayerType() == 'all')
            ) {

                $map = new TileLayer($this, $this->numTileLayers, $layer['data'], $layer['width'], $layer['height']);

                // generate open paths
                if (App::$generatePaths === true) {
                    $paths = new MapPaths($this, $this->numTileLayers, $layer['data'], $layer['width'], $layer['height']);
                }

                $this->numTileLayers++;
            }
            // object layer
            else if (
                $layer['type'] == 'objectgroup' &&
                (App::GetLayerType() == 'objectgroup' || App::GetLayerType() == 'all')
            ) {
                $map = new ObjectMap($this, $this->numObjectMaps, $layer);
                $this->numObjectMaps++;
            }

            // layer has been processed
            if ($map !== false) {
                // set name
                if ($groupName !== false) {
                    $map->SetName($groupName . '-' . $layer['name']);
                } else {
                    $map->SetName($layer['name']);
                }

                // add to maps array
                $this->maps[] = $map;
            } else {
                echo 'Error processing layer ' . $layer['name'] . '' . CR;
            }

            // paths layer
            if (App::$generatePaths == true) {
                if ($paths !== false) {
                    // set name
                    if ($groupName !== false) {
                        $paths->SetName($groupName . '-' . $layer['name'] . '-paths');
                    } else {
                        $paths->SetName($layer['name'] . '-paths');
                    }

                    // add to maps array
                    $this->maps[] = $paths;
                } else {
                    echo 'Error processing paths ' . $layer['name'] . '' . CR;
                }
            }
        }

        return true;
    }

    /**
     * Return the number of screens
     */
    public function GetNumTileLayers()
    {
        return $this->numTileLayers;
    }

    /**
     * Return the number of screens
     */
    public function GetNumObjectMaps()
    {
        return $this->numObjectMaps;
    }

    /**
     * Get code for all screens in currently set language
     */
    public function GetCode()
    {
        $str = '';

        for ($i = 0; $i < sizeof($this->maps); $i++) {

            switch (App::GetFormat()) {
                case 'c':
                    $str .= $this->GetCodeC($i);
                    break;
                default:
                    $str .= $this->GetCodeAsm($i);
                    break;
            }
        }
        return $str;
    }

    /**
     * Get binaries.lst file with list of screen files
     */
    public function GetBinariesLst()
    {
        $str = '';
        foreach ($this->maps as $map) {
            $str .= $map->GetCodeName() . CR;
        }
        return $str;
    }

    public function ProcessFile($filename)
    {
        // xml tilemap
        $success = $this->ReadFile($filename);

        if ($success === true) {

            // write tilemaps to files
            $count = 0;
            foreach ($this->maps as $map) {
                $map->WriteFile();
                $count++;
            }
        }
    }
}
