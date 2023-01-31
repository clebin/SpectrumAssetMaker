<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

/**
 * Class representing a tilemap with functions for reading and exporting
 */
class TilemapXML extends Tilemap
{
    /**
     * Read a tilemap XML file
     */
    public function ReadFile($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }

        $xml = file_get_contents($filename);
        $data = App::objectToArray(simplexml_load_string($xml));

        $this->ParseNode($data);
        return true;
    }

    public function ParseNode($node)
    {
        foreach ($node as $key => $val) {

            if ($key == 'layer') {
                $this->ReadTileLayerGroup($val);
            } else if ($key == 'objectgroup') {
                $this->ReadObjectMapGroup($val);
            } else if (is_array($val)) {
                $this->ParseNode($val);
            }
        }
    }

    /**
     * Read a simple file with only tilemap layers and no groups
     */
    public function ReadFileSimple($data)
    {
        // tile layers
        if (isset($data['layer'])) {
            return $this->ReadTileLayerGroup($data['layer']);
        }
        // object maps
        else if (isset($data['objectgroup'])) {
            $this->ReadObjectMapGroup($data['objectgroup']);
        }
    }

    public function ReadFileWithGroups($data)
    {
        // loop through groups
        $this->numTileLayers = 0;
        $this->numObjectMaps = 0;

        foreach ($data['group'] as $group) {

            // print_r($group);
            if (isset($group['@attributes']['name'])) {
                $groupName = $group['@attributes']['name'];

                // tile layers
                if (isset($group['layer'])) {
                    $this->ReadTileLayerGroup($group['layer'], $groupName);
                }
                // object maps
                else if (isset($group['objectgroup'])) {
                    $this->ReadObjectMapGroup($group['objectgroup'], $groupName);
                }
            } else {
                print_r($group);
            }
        }

        return true;
    }


    public function ReadTileLayerGroup($group, $groupName = false)
    {
        if ($groupName !== false) {
            echo 'Reading group "' . $groupName . '"' . CR;
        }

        // only one sub-layer
        if (isset($group['data'])) {
            $this->ReadTileLayer($group, $groupName);
        }
        // multiple sub-layers
        else {
            foreach ($group as $layer) {
                $this->ReadTileLayer($layer, $groupName);
            }
        }
    }

    public function ReadTileLayer($layer, $groupName = false)
    {
        $layerName = $layer['@attributes']['name'];

        echo 'Reading tile-layer "' . $layerName . '"' . CR;

        // is layer visible?
        if (isset($layer['@attributes']['visible']) && $layer['@attributes']['visible'] == false) {
            $visible = false;
        } else {
            $visible = true;
        }

        if (App::GetIgnoreHiddenLayers() === true && $visible === false) {
            return false;
        }

        $width = $layer['@attributes']['width'];
        $height = $layer['@attributes']['width'];
        $data = explode(',', $layer['data']);

        // process
        $map = new TileLayer($this, $this->numTileLayers, $data, $width, $height);
        $map->SetName($groupName . '-' . $layerName);

        // add to maps array
        if ($map !== false) {
            $this->AddToMapsArray($groupName, $layerName, $map);
            $this->numTileLayers++;
        } else {
            App::AddError('Error processing tile layer ' . $layerName);
        }
        return true;
    }

    public function ReadObjectMapGroup($group, $groupName = false)
    {
        if ($groupName !== false) {
            echo 'Reading group "' . $groupName . '"' . CR;
        }

        // only one sub-layer
        if (isset($group['data'])) {
            $this->ReadObjectMap($group, $groupName);
        }
        // multiple sub-layers
        else {
            foreach ($group as $layer) {
                $this->ReadObjectMap($layer, $groupName);
            }
        }
    }

    public function ReadObjectMap($layer, $groupName = false)
    {
        $layerName = $layer['@attributes']['name'];

        // is layer visible?
        if (isset($layer['@attributes']['visible']) && $layer['@attributes']['visible'] == false) {
            $visible = false;
        } else {
            $visible = true;
        }

        if (App::GetIgnoreHiddenLayers() === true && $visible === false) {
            return false;
        }

        $map = new ObjectMapXML($this, $this->numObjectMaps, $layer);
        $map->SetName($groupName . '-' . $layerName);

        if ($map !== false) {
            $this->AddToMapsArray($groupName, $layerName, $map);
            $this->numObjectMaps++;
        } else {
            App::AddError('Error processing object map ' . $layerName);
        }
    }

    public function AddToMapsArray($groupName, $layerName, $map)
    {
        // set name
        if ($groupName !== false) {
            $map->SetName($groupName . '-' . $layerName);
        } else {
            $map->SetName($layerName);
        }

        // add to maps array
        $this->maps[] = $map;
    }
}
