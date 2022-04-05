<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing an individual tile in a tileset
 */
class Tile
{
    // graphics data
    public $graphics = [];

    public $id = 0;

    // individual tile info
    public $paper = 0;
    public $ink = 7;
    public $bright = false;
    public $flash = false;

    // game properties
    public $solid = false;
    public $lethal = false;
    public $platform = false;
    public $custom = false;

    public function __construct($id, $properties)
    {
        // id
        $this->id = $id;

        // loop through properties
        foreach($properties as $prop) {

            switch( $prop['name'] ) {

                // attribute properties
                case 'paper':
                    $this->paper = intval($prop['value']);
                    break;
                case 'ink':
                    $this->ink = intval($prop['value']);
                    break;
                case 'bright':
                    $this->bright = intval($prop['value']);
                    break;
                case 'flash':
                    $this->flash = intval($prop['value']);
                    break;

                // game properties
                case 'solid':
                    $this->solid = intval($prop['value']);
                    SpecTiledTool::$saveGameProperties = true;
                    break;
                case 'lethal':
                    $this->lethal = intval($prop['value']);
                    SpecTiledTool::$saveGameProperties = true;
                    break;
                case 'platform':
                    $this->platform = intval($prop['value']);
                    SpecTiledTool::$saveGameProperties = true;
                    break;
                case 'custom':
                    $this->custom = intval($prop['value']);
                    SpecTiledTool::$saveGameProperties = true;
                    break;
            }
        }

        // set graphics
        //self::$graphics = Graphics::GetTileData($tile['num']);
    }
    
}