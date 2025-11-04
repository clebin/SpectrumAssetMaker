<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;
use \ClebinGames\SpectrumAssetMaker\Tile;

/**
 * Class representing a tileset with functions for reading and exporting
 */
class Tileset extends Datatype
{
    public string $datatypeName = 'Tileset';

    protected bool $tilesetIsSet = false;

    // property definitions
    public array $tilePropertyDefinitions = [];

    // default property definitions
    public static array $defaultTilePropertyDefinitions = [
        'colours' => [
            'flash', 
            'bright', 
            [
                'name' => 'paper',
                'length' => 3
            ],
            [
                'name' => 'ink',
                'length' => 3
            ]
        ]
    ];

    // array of tiles
    protected array $tiles = [];
    public bool $isLargeTileset = false;

    public function __construct($config)
    {
        parent::__construct($config);

        // set properties layout
        if(isset($config['custom-properties'])) {

            if( isset($config['custom-properties']['colours'])) {

                // colours set to true - inherit from defaults
                if( $config['custom-properties']['colours'] === true ) {

                    unset($config['tile-properties']['colours']);
                    $this->tilePropertyDefinitions = array_merge(
                        self::$defaultTilePropertyDefinitions,
                        $config['custom-properties']
                    );
                }
                // copy colours definitions from tileset config
                else {
                    
                    if( $config['custom-properties']['colours'] === false) {
                        unset($config['custom-properties']['colours']);
                    }
                    $this->tilePropertyDefinitions = $config['custom-properties'];
                }
            }

            $this->tilePropertyDefinitions = $config['custom-properties'];
        } else {
            $this->tilePropertyDefinitions = self::$defaultTilePropertyDefinitions;
        }

        // set sprite image
        if (isset($config['tileset'])) {
            $filename = $config['tileset'];
        } else {
            $this->isValid = false;
            return;
        }

        // repalace flash bit with solid (deprecated - use tile property definitions instead)
        if (isset($config['replace-flash-with-solid']) && $config['replace-flash-with-solid'] == true) {

            // update tile properties array
            $this->tilePropertyDefinitions['colours'][0] = 'solid';
        }

        $this->isValid = $this->ReadFile($filename);
    }

    /**
     * Set name and filename
     */
    public function SetName($name) : void
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
            $this->tiles[] = new Tile($id, $tile['properties'], $this->tilePropertyDefinitions);

            $count++;
        }

        // need to represent tile numbers with 16 bits
        if (sizeof($this->tiles) > 256) {
            $this->isLargeTileset = true;
        }

        App::OutputMessage($this->datatypeName, $this->name, 'Added ' . $count . ' tiles');

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

    public function TilesetIsSet() : bool
    {
        return $this->tilesetIsSet;
    }

    /**
     * Get number of tiles in tileset
     */
    public function GetNumTiles() : int
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
    public function GetCodeAsm() : string
    {
        $str = $this->GetHeaderAsm();

        // tile info
        foreach($this->tilePropertyDefinitions as $name => $info) {

            $asmArray = [];

            foreach($this->tiles as $tile) {

                $asmArray[] = $tile->GetPropertiesByte($name);
            }
        
            $codeName = App::GetConvertedCodeName($this->codeName . '-' . $name, $this->codeFormat);

            // colours
            $str .= App::GetAsmArray(
                $codeName,
                $asmArray,
                2
            ) . CR;
        }

        return $str;
    }
}
