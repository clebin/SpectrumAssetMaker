<?php

namespace ClebinGames\SpectrumAssetMaker;

/**
 * Class representing an individual tile in a tileset
 */
class Tile
{
    // graphics data
    public array $graphics = [];
    public bool $replaceFlashWithSolid = false;

    public int $id = 0;

    // individual tile info
    public int $paper = 0;
    public int $ink = 7;
    public bool $bright = false;
    public bool $flash = false;

    // game properties
    public bool $solid = false;
    public bool $lethal = false;
    public bool $platform = false;
    public bool $ladder = false;
    public bool $custom = false;

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
                        $this->bright = $prop['value'];
                        break;
                    case 'flash':
                        $this->flash = $prop['value'];
                        break;

                        // game properties
                    case 'solid':
                        $this->solid = $prop['value'];
                        App::$saveGameProperties = true;
                        break;
                    case 'lethal':
                        $this->lethal = $prop['value'];
                        break;
                    case 'ladder':
                        $this->ladder = $prop['value'];
                        break;
                    case 'platform':
                        $this->platform = $prop['value'];
                        break;
                    case 'custom':
                        $this->custom = $prop['value'];
                        break;
                }
            }
        }
    }

    /**
     * Return paper number for tile
     */
    public function GetPaper($id) : int
    {
        return $this->paper;
    }

    /**
     * Return ink number for tile
     */
    public function GetInk($id) : int
    {
        return $this->ink;
    }

    /**
     * Return whether bright is set on tile
     */
    public function isBright() : bool
    {
        return $this->bright;
    }

    /**
     * Return whether flash is set on tile
     */
    public function isFlash() : bool
    {
        return $this->flash;
    }

    /**
     * Return whether solid is set on tile
     */
    public function isSolid() : bool
    {
        return $this->solid;
    }

    /**
     * Return whether lethal is set on tile
     */
    public function isLethal() : bool
    {
        return $this->lethal;
    }

    /**
     * Return whether lethal is set on tile
     */
    public function isPlatform() : bool
    {
        return $this->platform;
    }

    /**
     * Return whether ladder is set on tile
     */
    public function isLadder() : bool
    {
        return $this->ladder;
    }

    /**
     * Return whether custom1 is set on tile
     */
    public function isCustom1() : bool
    {
        return $this->custom;
    }

    /**
     * Get byte containing flash, bright, paper and ink as a string
     */
    public function GetColoursByte() : string
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
    public function GetPropertiesByte() : string
    {
        return ($this->solid == true ? '1' : '0') .
            ($this->lethal == true ? '1' : '0') .
            ($this->platform == true ? '1' : '0') .
            ($this->custom == true ? '1' : '0') .
            '0000';
    }
}
