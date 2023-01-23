<?php

namespace ClebinGames\SpectrumAssetMaker;

/**
 * Class representing an individual tile in a tileset
 */
class Tile
{
    // graphics data
    public $graphics = [];
    public $replaceFlashWithSolid = false;

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

    public function __construct($id, $properties, $replaceFlashWithSolid = false)
    {
        // id
        $this->id = $id;

        // replace flash bit with solid property
        $this->replaceFlashWithSolid = $replaceFlashWithSolid;

        // loop through properties
        if (is_array($properties)) {
            foreach ($properties as $prop) {

                switch ($prop['name']) {

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
                        App::$saveGameProperties = true;
                        break;
                    case 'lethal':
                        $this->lethal = intval($prop['value']);
                        App::$saveGameProperties = true;
                        break;
                    case 'platform':
                        $this->platform = intval($prop['value']);
                        App::$saveGameProperties = true;
                        break;
                    case 'custom':
                        $this->custom = intval($prop['value']);
                        App::$saveGameProperties = true;
                        break;
                }
            }
        }
    }

    /**
     * Return paper number for tile
     */
    public function GetPaper($id)
    {
        return $this->paper;
    }

    /**
     * Return ink number for tile
     */
    public function GetInk($id)
    {
        return $this->ink;
    }

    /**
     * Return whether bright is set on tile
     */
    public function GetBright()
    {
        return $this->bright;
    }

    /**
     * Return whether flash is set on tile
     */
    public function GetFlash()
    {
        return $this->flash;
    }

    /**
     * Return whether solid is set on tile
     */
    public function GetSolid()
    {
        return $this->solid;
    }

    /**
     * Return whether lethal is set on tile
     */
    public function GetLethal()
    {
        return $this->lethal;
    }

    /**
     * Return whether lethal is set on tile
     */
    public function GetPlatform()
    {
        return $this->platform;
    }

    /**
     * Return whether custom1 is set on tile
     */
    public function GetCustom1()
    {
        return $this->custom;
    }

    /**
     * Get byte containing flash, bright, paper and ink as a string
     */
    public function GetColoursByte()
    {
        $str = '';

        if ($this->replaceFlashWithSolid === true) {
            $str .= ($this->solid == true ? '1' : '0');
        } else {
            $str .= ($this->flash == true ? '1' : '0');
        }

        return $str .
            ($this->bright == true ? '1' : '0') . // bright
            str_pad(decbin($this->paper), 3, '0', STR_PAD_LEFT) .
            str_pad(decbin($this->ink), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get byte containing solid, lethal, platform, custom
     * variables as a string
     */
    public function GetPropertiesByte()
    {
        return ($this->solid == true ? '1' : '0') .
            ($this->lethal == true ? '1' : '0') .
            ($this->platform == true ? '1' : '0') .
            ($this->custom == true ? '1' : '0') .
            '0000';
    }
}
