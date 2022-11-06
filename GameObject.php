<?php

namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tilemap game object
 */
class GameObject
{
    private $type = '';
    private $index = false;
    private $row = 0;
    private $col = 0;
    private $width = 0;
    private $height = 0;
    private $customProperties = [];
    // private $zOrder = 0;

    public function __construct($json)
    {
        $this->type = strval($json['class']);
        $this->row = intval($json['y'] / 8);
        $this->col = intval($json['x'] / 8);
        $this->width = intval($json['width'] / 8);
        $this->height = intval($json['height'] / 8);

        // name (optional)
        if ($json['name'] != '') {
            $this->name = $json['name'];
        }

        // read custom properties
        if (isset($json['properties'])) {
            foreach ($json['properties'] as $prop) {
                $this->customProperties[$prop['name']] = $prop['value'];
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

    public function GetWidth()
    {
        return $this->width;
    }

    public function GetHeight()
    {
        return $this->height;
    }

    public function GetCustomProperty($name)
    {
        // property is set
        if (isset($this->customProperties[$name])) {
            return $this->customProperties[$name];
        }

        // return false if not set
        return 0;
    }

    public function IsUsingDimensions()
    {
        return $this->addDimensions;
    }
}
