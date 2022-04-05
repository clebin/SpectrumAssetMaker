<?php
namespace ClebinGames\SpecTiledTool;

class Attribute
{
    public $tileNum = 0;
    public $bright = false;
    public $flash = false;
    public $paper = 0;
    public $ink = 7;

    public function __construct($tileNum = 0, $flash = false, $bright = false, $paper = 0, $ink = 7) {

        $this->tileNum = $tileNum;
        $this->flash = $flash;
        $this->bright = $bright;
        $this->paper = $paper;
        $this->ink = $ink;
    }
}
