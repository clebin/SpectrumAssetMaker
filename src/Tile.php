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

    // game properties
    public array $properties = [];
    public array $propertiesDefinitions = [];

    public function __construct($id, $sourceProperties, $propertyDefinitions)
    {
        // id
        $this->id = $id;

        // property definitions
        $this->propertiesDefinitions = $propertyDefinitions;

        // read all properties
        foreach($sourceProperties as &$prop) {

            if( $prop['type'] == 'bool') {
                
                if( $prop['value'] == 1) {
                    $prop['value'] = true;
                } else {
                    $prop['value'] = false;
                }
            }

            $this->properties[$prop['name']] = $prop['value'];
        }
    }

    /**
     * Check if it's a ladder - use source properties in case we're not saving this
     */
    public function IsLadder() : bool
    {
        if( isset($this->properties['ladder']) && 
            $this->properties['ladder'] === true ) {
                return true;
        }
        return false;
    }

    /**
     * Check if it's solid - use source properties in case we're not saving this
     */
    public function IsSolid() : bool
    {
        if( isset($this->properties['solid']) && 
            $this->properties['solid'] === true ) {
                return true;
        }
        return false;
    }

    /* 
     * Get a tile property
     */
    public function GetProperty($propName, $arrayName = false) : bool
    {
        // no array specified, go searching
        if( $arrayName === false ) {

            foreach($this->properties as $array) {
                foreach($array as $prop) {
                    if($prop['name'] == $propName) {
                        return $prop['value'];
                    }
                }
            }
        }
        // array specified
        else if(isset($this->properties[$arrayName][$propName])) {

            return $this->properties[$arrayName][$propName];
        }

        return false;
    }

    /**
     * Get byte containing flash, bright, paper and ink as a string
     */
    public function GetPropertiesByte($name) : string
    {
        $str = '';

        foreach($this->propertiesDefinitions[$name] as $prop) {

            // property is not an array, treat as a boolean
            if( !is_array($prop)) {
                $prop = [
                    'name' => $prop,
                    'length' => 1
                ];
            }

            // get value
            if( isset($this->properties[$prop['name']])) {
                $value = $this->properties[$prop['name']];
            } else {
                $value = 0;
            }

            if( $prop['length'] > 1 ) {
                echo 'hmm'.$prop['name'].' - '.$value.CR;
                $str .= str_pad(decbin($value), $prop['length'], '0', STR_PAD_LEFT);
            } else {
                $str .= ($value == true || $value == 1 ? '1' : '0');
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
