<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tileset with functions for reading and exporting
 */
class Tileset
{
    // static array of tiles
    private static $tiles = [];

    /**
     * Read the tileset JSON file
     */
    public static function ReadFile($filename)
    {
        if(!file_exists($filename)) {
            return false;
        }
        
        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        $count = 0;
        foreach($data['tiles'] as $tile) {

            $id = intval($tile['id']);

            // save to tiles array using id as key
            self::$tiles[] = new Tile($id, $tile['properties']);

            $count++;
        }
        echo CR;

        echo 'Added '.$count.' tiles.'.CR;
        return true;
    }
    
    /**
     * Get number of tiles in tileset
     */
    public static function GetNumTiles()
    {
        return sizeof(self::$tiles);
    }

    /**
     * Return whether a tile with a particular id exists in the tileset
     */
    public static function TileExists($id){
        return isset(self::$tiles[$id]);
    }

    /**
     * Get tile graphics code in currently set format/language
     */
    public static function GetCode()
    {
        switch( SpecTiledTool::GetFormat() ) {
            
            case 'c':
                return self::GetC();
                break;

            default:
                return self::GetAsm();
                break;
        }
    }

    /**
     * Return tilset in Assembly format
     */
    public static function GetAsm()
    {
        $str = 'Error: Assembly tileset export is not supported.';

        return $str;
    }

    /**
     * Return C array of tile colours and properties
     */
    public static function GetC()
    {
        $str = '';

        if( SpecTiledTool::GetPrefix() !== false ) {
            $baseName = SpecTiledTool::GetPrefix().'Tileset';
        } else {
            $baseName = 'tileset';
        }

        $str .= '#define '.strtoupper($baseName).'_LEN '.sizeof(self::$tiles).CR.CR;

        // tile info
        $colours = [];
        $properties = [];
        foreach(self::$tiles as $tile) {
            $colours[] = $tile->GetColoursByte();
            $properties[] = $tile->GetPropertiesByte();
        }
        
        // colours
        $str .= SpecTiledTool::GetCArray(
            $baseName.'Colours', 
            $colours, 
            2
        ).CR;

        // properties
        $str .= SpecTiledTool::GetCArray(
            $baseName.'Properties', 
            $properties, 
            2
        ).CR;

        return $str;
    }
}
