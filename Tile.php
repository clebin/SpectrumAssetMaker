<?php
namespace ClebinGames\SpecTiledTool;

class Tile
{
    // graphics data
    public $graphics = [];

    public $id = 0;

    // individual tile info
    public $paper = 0;
    public $ink = 7;
    public $bright = false;

    // game properties
    public $solid = false;
    public $lethal = false;

    public function __construct($id, $properties)
    {
        // id
        $this->id = $id;

        // loop through properties
        foreach($properties as $prop) {

            // paper
            if( $prop['name'] == 'paper' ) {
                $this->paper = intval($prop['value']);
            }
            // ink
            elseif( $prop['name'] == 'ink' ) {
                $this->ink = intval($prop['value']);
            }
            // bright
            elseif( $prop['name'] == 'bright' ) {
                $this->bright = $prop['value'];
            }
        }

        // set graphics
        //self::$graphics = Graphics::GetTileData($tile['num']);

    }
    
}