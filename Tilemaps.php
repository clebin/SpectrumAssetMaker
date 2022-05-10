<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tilemap with functions for reading and exporting
 */
class Tilemaps {
    
    // data arrays
    public static $maps = [];

    public static $numTilemaps = 0;
    public static $numObjectMaps = 0;

    public static $defineName = 'TILEMAPS_LEN';
    public static $width = false;
    public static $height = false;

    // allowed properties on enemies, objects, etc.
    private static $object_allowed_properties = [
        'collectable',
        'deltax', 
        'deltay', 
        'speed', 
        'numhits', 
        'transient', 
        'lethal', 
        'endval', 
        'movement'
    ];

    /**
     * Read the tilemap JSON file.
     */
    public static function ReadFile($filename) {

        if(!file_exists($filename)) {
            return false;
        }

        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        // set name for #define screens length
        if( SpecTiledTool::GetPrefix() !== false ) {
            self::$defineName = strtoupper(SpecTiledTool::GetPrefix()).'_'.self::$defineName;
        }

        // read simple
        if( isset($data['layers'][0]['layers']) ) {
            $success = self::ReadFileWithGroups($data);
        }
        // read with object layers
        else {
            $success = self::ReadFileSimple($data);
        }

        return $success;
    }

    /**
     * Read a simple file with only tilemap layers and no groups
     */
    public static function ReadFileSimple($data)
    {
        return self::ReadLayerGroup($data['layers']);
    }

    public static function ReadFileWithGroups($data)
    {
        // loop through groups
        self::$numTilemaps = 0;
        self::$numObjectMaps = 0;

        foreach($data['layers'] as $group) {
            self::ReadLayerGroup($group['layers'], $group['name']);
        }

        return true;
    }

    public static function ReadLayerGroup($group, $name = false)
    {
        foreach($group as $layer) {

            // tilemap
            if( $layer['type'] == 'tilelayer' ) {
                $map = new Tilemap(self::$numTilemaps, $layer);
                self::$numTilemaps++;
            }
            // objects
            else if( $layer['type'] == 'objectgroup' ) {
                $map = new ObjectMap(self::$numObjectMaps, $layer);
                self::$numObjectMaps++;
            }
            else {
                $map = false;
            }

            if($map !== false) {

                $map->SetDimensions(self::$width, self::$height);

                // set name
                if( $name !== false ) {
                    $map->SetName($name);
                } else {
                    $map->SetName($layer['name']);
                }

                self::$maps[] = $map;
            }
        }

        return true;
    }
    
    /**
     * Return the number of screens
     */
    public static function GetNumTilemaps()
    {
        return self::$numTilemaps;
    }
    
    /**
     * Return the number of screens
     */
    public static function GetNumObjectMaps()
    {
        return self::$numObjectMaps;
    }

    /**
     * Get code for all screens in currently set language
     */
    public static function GetCode()
    {
        $str = '';

        for($i=0;$i<sizeof(self::$maps);$i++) {

            switch( SpecTiledTool::GetFormat() ) {
                case 'c':
                    $str .= self::GetCodeC($i);
                    break;
                default:
                    $str .= self::GetCodeAsm($i);
                    break;
            }
        }
        return $str;
    }

    /**
     * Get binaries.lst file with list of screen files
     */
    public static function GetBinariesLst()
    {
        $str = '';
        foreach(self::$maps as $map) {
            $str .= $map->GetCodeName().CR;
        }
        return $str;
    }

    public static function Process($filename)
    {
        // read map and tilset
        $success = self::ReadFile($filename);
        
        if( $success === true ) {

            // write tilemaps to files
            $count = 0;
            foreach(self::$maps as $map) {
                file_put_contents($map->GetOutputFilename(), $map->GetCode());
                $count++;
            }
        }
    }
}
