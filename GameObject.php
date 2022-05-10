<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tilemap game object
 */
class GameObject {

    public $name = '';
    public $index = 0;
    public $row = 0;
    public $col = 0;
    public $width = 0;
    public $height = 0;

    public function __construct($index, $row, $col)
    {
        $this->index = intval($index);
        $this->row = intval($row);
        $this->col = intval($col);
    }

    public function GetIndex()
    {
        return $this->index;
    }

    public function GetRow()
    {
        return $this->row;
    }

    public function GetCol()
    {
        return $this->col;
    }
}
