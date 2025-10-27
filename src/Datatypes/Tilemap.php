<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

/**
 * Class representing a tilemap with functions for reading and exporting
 */
class Tilemap extends Datatype
{
    public string $datatypeName = 'Tilemap';

    // data arrays
    public array $maps = [];

    public int $numTileLayers = 0;
    public int $numObjectMaps = 0;

    public string $defineName = 'TILEMAPS_LEN';
    public int|false $width = false;
    public int|false $height = false;

    public $objectTypes = false;
    public bool $ignoreHiddenLayers = false;
    public string $layerTypes = App::LAYER_TYPE_ALL;
    public bool $generatePaths = false;
    public bool $addDimensions = false;
    public $tileset = false;

    // allowed properties on enemies, objects, etc.
    private array $object_allowed_properties = [
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

    public function __construct($config)
    {
        parent::__construct($config);

        // ignore hidden layers
        if (isset($config['ignore-hidden-layers']) && $config['ignore-hidden-layers'] === true) {
            $this->ignoreHiddenLayers = true;
        }

        // object types
        if (isset($config['object-types'])) {
            $this->objectTypes = $config['object-types'];
        }

        // generate paths
        if (isset($config['generate-paths']) && $config['generate-paths'] === true) {
            $this->generatePaths = true;
        }

        // layer types
        if (isset($config['layer-types']) && in_array($config['layer-types'], App::$layerTypesSupported)) {
            $this->layerTypes = $config['layer-types'];
        }

        // add dimensions
        if (isset($config['add-dimensions']) && in_array($config['add-dimensions'], App::$layerTypesSupported)) {
            $this->addDimensions = $config['add-dimensions'];
        }

        // add associated tileset
        if (isset($config['tileset']) && $config['tileset'] !== false) {

            if (is_array($config['tileset'])) {
                $this->ReadTilesetWithConfig($config['tileset']);
            } else {
                $this->tileset = new Tileset($config);
                $this->tileset->Process();
            }
        }

        // read tilemap
        if (isset($config['map'])) {
            $this->isValid = $this->ReadFile($config['map']);
        }
    }

    /**
     * Read an associated tileset
     */
    public function ReadTilesetWithConfig($tileset_config)
    {
        $this->tileset = new Tileset(array_merge([
            'name' => $this->name,
            'section' => $this->codeSection,
            'format' => $this->codeFormat,
            'output-folder' => $this->outputFolder
        ], $tileset_config));

        $this->tileset->Process();
    }

    /**
     * Read a tilemap JSON file.
     */
    public function ReadFile($filename)
    {
        if (!file_exists($filename)) {
            App::AddError('Tilemap file (' . $filename . ') not found');
            return false;
        }

        $this->filename = $filename;

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

            if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
                App::OutputMessage('Map layer', $layer['name'], 'Reading ' . $layer['type']);
            }

            // tilemap
            if ($this->ignoreHiddenLayers === true && $layer['hidden'] === true) {
                // do nothing
                $map = false;
                $paths = false;
            }
            // tile layer
            else if (
                $layer['type'] == App::LAYER_TYPE_TILELAYER &&
                ($this->layerTypes == App::LAYER_TYPE_TILELAYER || $this->layerTypes == App::LAYER_TYPE_ALL)
            ) {

                $map = new TileLayer([
                    'tilemap' => $this,
                    'num' => $this->numTileLayers,
                    'data' => $layer['data'],
                    'width' => $layer['width'],
                    'height' => $layer['height'],
                    'add-dimensions' => $this->addDimensions,
                    'compression' => $this->compression,
                    'format' => $this->codeFormat,
                    'section' => $this->codeSection,
                    'output-folder' => $this->outputFolder
                ]);

                // generate open paths
                if ($this->generatePaths === true) {

                    $paths = new MapPaths(
                        [
                            'tilemap' => $this,
                            'tileset_obj' => $this->tileset,
                            'num' => $this->numTileLayers,
                            'data' => $layer['data'],
                            'width' => $layer['width'],
                            'height' => $layer['height'],
                            'add-dimensions' => $this->addDimensions,
                            'compression' => $this->compression,
                            'section' => $this->codeSection,
                            'format' => $this->codeFormat,
                            'output-folder' => $this->outputFolder
                        ]
                    );
                }

                $this->numTileLayers++;
            }
            // object layer
            else if (
                $layer['type'] == App::LAYER_TYPE_OBJECTGROUP &&
                ($this->layerTypes == App::LAYER_TYPE_OBJECTGROUP || $this->layerTypes == App::LAYER_TYPE_ALL)
            ) {
                $map = new ObjectMap([
                    'tilemap' => $this,
                    'num' => $this->numObjectMaps,
                    'layer' => $layer,
                    'object-types' => $this->objectTypes,
                    'format' => $this->codeFormat,
                    'section' => $this->codeSection,
                    'output-folder' => $this->outputFolder
                ]);
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
                echo 'Error: Couldn\'t process layer ' . $layer['name'] . '' . CR;
            }

            // paths layer
            if ($this->generatePaths == true) {
                if ($paths !== false) {
                    // set name
                    if ($groupName !== false) {
                        $paths->SetName($groupName . '-' . $layer['name'] . '-paths');
                    } else {
                        $paths->SetName($layer['name'] . '-paths');
                    }

                    // add to maps array
                    $this->maps[] = $paths;
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

            switch ($this->codeFormat) {
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

    public function Process() : void
    {
        if ($this->isValid === true) {

            // write tilemaps to files
            $count = 0;
            foreach ($this->maps as $map) {
                $map->WriteFile();
                $count++;
            }
        }
    }
}
