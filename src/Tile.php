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

    // source properties - in case we're not importing all tiled properties
    // but want to make use of them, eg. ladders for generating path maps
    public array $sourceProperties = [];

    // game properties
    public array $properties = [];

    public function __construct($id, $sourceProperties, $propertyDefinitions)
    {
        // id
        $this->id = $id;

        // property definitions
        $this->properties = $propertyDefinitions;

        // App::$saveGameProperties = true;

        // fill in values
        foreach($this->properties as &$propertiesArray) {

            foreach($propertiesArray as &$prop) {

                // find value in tiled properties
                $value = false;

                // skip this value
                if( $prop === false ) {
                    $prop = [
                        'name' => false,
                        'length' => 1,
                        'value' => false
                    ];
                }
                else {

                    // property definition is not an array
                    if( !is_array($prop)) {
                        $prop = [
                            'name' => strval($prop),
                            'length' => 1
                        ];
                    }

                    // loop through property definitions
                    foreach($sourceProperties as $sourceProp) {

                        if( $sourceProp['name'] == $prop['name'] ) {
                            $value = $sourceProp['value'];
                        }

                        $this->sourceProperties[$prop['name']] = $value;
                    }

                    $prop['value'] = $value;
                }

            }
        }

    }

    /**
     * Check if it's a ladder - use source properties in case we're not saving this
     */
    public function IsLadder() : bool
    {
        if( isset($this->sourceProperties['ladder']) && 
            $this->sourceProperties['ladder'] === true ) {
                return true;
        }
        return false;
    }

    /**
     * Check if it's solid - use source properties in case we're not saving this
     */
    public function IsSolid() : bool
    {
        if( isset($this->sourceProperties['solid']) && 
            $this->sourceProperties['solid'] === true ) {
                return true;
        }
        return false;
    }

    /* 
     * Get a tile property
     */
    public function GetProperties($name, $array = false) : bool
    {
        // no array specified, go searching
        if( $array === false ) {

            foreach($this->properties as $array) {
                foreach($array as $prop) {
                    if($prop['name'] == $name) {
                        return $prop['value'];
                    }
                }
            }
        }
        // array specified
        else if(isset($this->properties[$array][$name])) {

            return $this->properties[$array][$name];
        }

        return false;
    }

    /**
     * Get byte containing flash, bright, paper and ink as a string
     */
    public function GetPropertiesByte($name) : string
    {
        if( !isset($this->properties[$name]) ) {
            return false;
        }

        $str = '';

        foreach($this->properties[$name] as $prop) {

            if( $prop['length'] > 1 ) {
                $str .= str_pad(decbin($prop['value']), $prop['length'], '0', STR_PAD_LEFT);
            } else {
                $str .= ($prop['value'] == true || $prop['value'] == 1 ? '1' : '0');
            }
        }

        $strlen = strlen($str);

        // reduce to one byte
        if( $strlen > 8 ) {
            $str = substr($str, 0, 8);
        }
        // pad to one byte
        else {
            $str = str_pad($str, 8, '0', STR_PAD_RIGHT);
        }

        return $str;
    }
}
