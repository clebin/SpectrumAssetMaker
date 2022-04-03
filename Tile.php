<?php
namespace ClebinGames\SpecScreenTool;

class Tile
{
    // graphics data
    public $graphics = [];

    // individual tile info
    public $paper = 0;
    public $ink = 7;
    public $bright = false;

    // game properties
    public $solid = false;
    public $lethal = false;

    public function __construct($tile)
    {
        // set graphics
        //self::$graphics = Graphics::GetTileData($tile['num']);

        // set paper
        if( isset($tile['paper'])) {

            $paper = intval($tile['paper']);
            if( $paper >= 0 && $paper <= 7 ) {
                $this->paper = $paper;
            }
        }

        // set ink
        if( isset($tile['ink'])) {

            $ink = intval($tile['ink']);
            if( $ink >= 0 && $ink <= 7 ) {
                $this->ink = $ink;
            }
        }

        // set bright
        if( isset($tile['bright'])) {

            if( $tile['bright'] === true ) {
                $this->bright = true;
            }
        }
    }
    
}