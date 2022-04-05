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
            SpecTiledTool::AddError('Tileset file not found');
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
    }
    
    /**
     * Output assembly code for tiles
     */
    private static function GetAsm()
    {
        $str = '._'.SpecTiledTool::GetPrefix().'_'.$num.CR;

        foreach($lines as $line) {
            $str .= 'defb @'.implode('', $line).CR;
        }

        return $str.CR;
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
     * Return paper number for tile
     */
    public static function GetPaper($id)
    {
        return self::$tiles[$id]->paper;
    }

    /**
     * Return ink number for tile
     */
    public static function GetInk($id)
    {
        return self::$tiles[$id]->ink;
    }

    /**
     * Return whether bright is set on tile
     */
    public static function GetBright($id)
    {
        return self::$tiles[$id]->bright;
    }

    /**
     * Return whether flash is set on tile
     */
    public static function GetFlash($id)
    {
        return self::$tiles[$id]->flash;
    }

    /**
     * Return whether solid is set on tile
     */
    public static function GetSolid($id)
    {
        return self::$tiles[$id]->solid;
    }

    /**
     * Return whether lethal is set on tile
     */
    public static function GetLethal($id)
    {
        return self::$tiles[$id]->lethal;
    }
}
