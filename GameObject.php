<?php

namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tilemap game object
 */
class GameObject
{
    private $type = '';
    private $index = 0;
    private $row = 0;
    private $col = 0;
    private $width = 0;
    private $height = 0;
    private $zOrder = 0;
    private $custom_properties = [];

    public function __construct($json)
    {
        $this->type = strval($json['class']);
        $this->row = intval($json['y'] / 8);
        $this->col = intval($json['x'] / 8);

        // name (optional)
        if ($json['name'] != '') {
            $this->name = $json['name'];
        }

        // read custom properties
        if (isset($json['properties'])) {
            foreach ($json['properties'] as $prop) {
                $this->custom_properties[$prop['name']] = $prop['value'];
            }
        }

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

    public function GetCustomProperty($name)
    {
        // property is set
        if (isset($this->custom_properties[$name])) {
            return $this->custom_properties[$name];
        }

        // return false if not set
        return false;
    }
}
