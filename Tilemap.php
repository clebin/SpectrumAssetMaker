<?php
namespace ClebinGames\SpecScreenTool;

class Tilemap {

    public static $prefix = 'tile';

    // data arrays
    private static $tileMap = [];
    private static $paperMap = [];
    private static $inkMap = [];
    private static $brightMap = [];

    /**
     * Read the tilemap JSON file.
     */
    public static function ReadFile($filename) {

        if(!file_exists($filename)) {

            SpecScreenTool::AddError('Map file not found');
            return false;
        }

        $json = readfile($filename);
        
        self::$tileMap = json_decode($json);
    }

    /**
     * Get the assembly code for this tilemap
     */
    public static function GetAsm()
    {
        // output tile numbers

        // output paper

        // output ink

        // output bright

        // output solid map

        // output lethal map
    }

    /**
     * Save screen attribute information. Save various properties in individual arrays.
     */
    private static function SaveAttributes() {
        
        foreach(self::$tileMap as $tileNum) {
            self::$paperMap[] = Tile::GetPaper($tileNum);
            self::$inkMap[] = Tile::GetInk($tileNum);
            self::$brightMap[] = Tile::GetBright($tileNum);
            self::$solidMap[] = Tile::GetSolid($tileNum);
            self::$lethalMap[] = Tile::GetLethal($tileNum);
        }
    }
}