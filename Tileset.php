<?php
namespace ClebinGames\SpecScreenTool;

class Tileset
{
    // static array of tiles
    public static $tiles = [];

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
        foreach($data as $tile) {
            //$tile['num'] = $count;
            Tileset::AddTile($tile);
            $count++;
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
    public static function GetPaper($num)
    {
        return self::$tiles[$num]->paper;
    }

    public static function GetInk($num)
    {
        return self::$tiles[$num]->ink;
    }

    public static function GetBright($num)
    {
        return self::$tiles[$num]->bright;
    }

    public static function GetSolid($num)
    {
        return self::$tiles[$num]->solid;
    }

    public static function GetLethal($num)
    {
        return self::$tiles[$num]->lethal;
    }

    public static function AddTile($tile)
    {
        self::$tiles[] = new Tile($tile);
    }
}
