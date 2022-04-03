<?php
namespace ClebinGames\SpecScreenTool;

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
            SpecScreenTool::AddError('Tileset file not found');
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
    }
    
    /**
     * Output assembly code for tiles
     */
    private static function GetAsm()
    {
        $str = '._'.SpecScreenTool::GetPrefix().'_'.$num.CR;

        foreach($lines as $line) {
            $str .= 'defb @'.implode('', $line).CR;
        }

        return $str.CR;
    }

    public static function GetNumTiles()
    {
        return sizeof(self::$tiles);
    }

    public static function TileExists($id){
        return isset(self::$tiles[$id]);
    }

    /* static functions */
    public static function GetPaper($id)
    {
        return self::$tiles[$id]->paper;
    }

    public static function GetInk($id)
    {
        return self::$tiles[$id]->ink;
    }

    public static function GetBright($id)
    {
        return self::$tiles[$id]->bright;
    }

    public static function GetSolid($id)
    {
        return self::$tiles[$id]->solid;
    }

    public static function GetLethal($id)
    {
        return self::$tiles[$id]->lethal;
    }
}
