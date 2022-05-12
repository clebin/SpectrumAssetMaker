<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tilemap game object
 */
class GameObject {

    public $type = '';
    public $index = 0;
    public $row = 0;
    public $col = 0;
    public $width = 0;
    public $height = 0;
    public $zOrder = 0;

    public function __construct($json)
    {
        $this->type = strval($json['type']);
        $this->row = intval($json['y']/8);
        $this->col = intval($json['x']/8);

        // name (optional)
        if( $json['name'] != '' ) {
            $this->name = $json['name'];
        }

        // read properties
        if( isset($json['properties']) ) {
            foreach($json['properties'] as $prop) {

                // z-order
                if( strtolower(str_replace('-', '', $prop['name'])) == 'zorder' ) {
                    $this->zOrder = $prop['value'];
                }
            }
        }

        // zOrder
        // if( isset($json['properties']))

        // loop up index
        $this->index = ObjectTypes::GetIndex($this->type);
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
