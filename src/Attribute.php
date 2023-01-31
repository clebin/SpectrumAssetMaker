<?php

namespace ClebinGames\SpectrumAssetMaker;

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

    // game properties
    public $solid = false;
    public $lethal = false;
    public $platform = false;
    public $custom = false;

    public $strValue = 0;
    public $value = 0;

    public function __construct(
        $paper = 0,
        $ink = 7,
        $bright = false,
        $flash = false,
    ) {
        $this->paper = $paper;
        $this->ink = $ink;
        $this->bright = $bright;
        $this->flash = $flash;

        $ink = str_pad(decbin($this->ink), 3, '0', STR_PAD_LEFT);
        $paper = str_pad(decbin($this->paper), 3, '0', STR_PAD_LEFT);

        $this->strValue = ($flash === true ? '1' : '0') . ($bright === true ? '1' : '0') . $paper . $ink;
        $this->value = bindec($this->strValue);
    }

    public function GetPaper()
    {
        return $this->paper;
    }

    public function GetInk()
    {
        return $this->ink;
    }

    public function GetValue()
    {
        return $this->value;
    }
}
