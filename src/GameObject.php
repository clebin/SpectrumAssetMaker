<?php

namespace ClebinGames\SpectrumAssetMaker;

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

    public function __construct($data)
    {
        // get object type
        if (isset($data['class'])) {
            $this->type = strval($data['class']);
        } else if (isset($data['type'])) {
            $this->type = strval($data['type']);
        } else {
            $this->type = strval($data['name']);
        }

        $this->row = intval($data['y'] / 8);
        $this->col = intval($data['x'] / 8);
        $this->width = intval($data['width'] / 8);
        $this->height = intval($data['height'] / 8);

        // name (optional)
        if (!isset($data['name'])) {
            print_r($data);
            exit();
            return false;
        }
        $this->name = $data['name'];

        // read custom properties
        if (isset($data['properties'])) {
            foreach ($data['properties'] as $prop) {
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
