<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a screen attribute
 */
class Attribute
{
    public $tileNum = 0;
    public $bright = false;
    public $flash = false;
    public $paper = 0;
    public $ink = 7;

    public $solid = false;
    public $lethal = false;
    public $platform = false;
    public $custom = false;
    
    public function __construct(
        $tileNum = 0, 
        $flash = false, 
        $bright = false, 
        $paper = 0, 
        $ink = 7, 
        $solid = false, 
        $lethal = false, 
        $platform = false, 
        $custom = false
        ) {
        
        $this->tileNum = $tileNum;
        $this->flash = $flash;
        $this->bright = $bright;
        $this->paper = $paper;
        $this->ink = $ink;

        // game properties
        $this->solid = $solid;
        $this->lethal = $lethal;
        $this->platform = $platform;
        $this->custom = $custom;
    }
}
