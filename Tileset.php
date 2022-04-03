<?php
namespace ClebinGames\SpecScreenTool;

class Tileset {

    // static array of tiles
    public static $tiles = [];

    /**
     * Read the tileset JSON file
     */
    public static function ReadFile($filename) {

        if(!file_exists($filename)) {
            SpecScreenTool::AddError('Tileset file not found');
            return false;
        }
        
        $json = readfile($filename);
        $data = json_decode($json);

        foreach($data as $tile) {
            Tile::AddTile($tile);
        }
    }

    /**
     * Output assembly code for tiles
     */
    private static function GetAsm()
    {
        $str = '._'.$prefix.'_'.$num.CR;

        foreach($lines as $line) {
            $str .= 'defb @'.implode('', $line).CR;
        }

        return $str.CR;
    }

    /* static functions */
    public static function GetPaper($num) {
        return self::$tiles[$num]->paper;
    }

    public static function GetInk($num) {
        return self::$tiles[$num]->ink;
    }

    public static function GetBright($num) {
        return self::$tiles[$num]->bright;
    }

    public static function GetSolid($num) {
        return self::$tiles[$num]->solid;
    }

    public static function GetLethal($num) {
        return self::$tiles[$num]->lethal;
    }

    public static function AddTile($tile) {
        self::$tiles[] = new Tile($tile);
    }
}
