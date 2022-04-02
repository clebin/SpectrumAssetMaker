<?php
namespace ClebinGames\SpecScreenTool;

define('CR', "\n");

class Tile {

    // static array of tiles
    public static $tiles = [];

    // individual tile info
    public $paper = 0;
    public $ink = 7;
    public $bright = false;

    /* static functions */
    public static function GetTilePaper($num) {
        return self::$tiles[$num]->paper;
    }

    public static function GetTileInk($num) {
        return self::$tiles[$num]->ink;
    }

    public static function GetTileBright($num) {
        return self::$tiles[$num]->bright;
    }

    public static function AddTile($tile) {
        self::$tiles[] = new Tile($tile);
    }

    public function __construct($tile) {

        // set paper
        if( isset($tile['paper'])) {

            $paper = intval($tile['paper']);
            if( $paper >= 0 && $paper <= 7 ) {
                $this->paper = $paper;
            }
        }

        // set ink
        if( isset($tile['ink'])) {

            $ink = intval($tile['ink']);
            if( $ink >= 0 && $ink <= 7 ) {
                $this->ink = $ink;
            }
        }

        // set bright
        if( isset($tile['bright'])) {

            if( $tile['bright'] === true ) {
                $this->bright = true;
            }
        }
    }
}

class SpecScreenTool {

    // did an error occur?
    private static $error = false;
    private static $errorDetails = '';

    // filenames
    private static $mapFilename = false;
    private static $tilesetFilename = false;
    private static $outputFilename = false;

    // data arrays
    private static $tileMap = [];
    private static $paperMap = [];
    private static $inkMap = [];
    private static $brightMap = [];

    // output
    private static $output = '';

    public static function Run($options) {

        // map
        if( isset($options['m'])) {
            self::$mapFilename = $options['m'];
        } else if( isset($options['map'])) {
            self::$mapFilename = $options['map'];
        }

        // tileset
        if( isset($options['t'])) {
            self::$tilesetFilename = $options['t'];
        } else if( isset($options['tileset'])) {
            self::$tilesetFilename = $options['tileset'];
        }

        // output
        if( isset($options['o'])) {
            self::$outputFilename = $options['o'];
        } else if( isset($options['output'])) {
            self::$outputFilename = $options['output'];
        }

        // tileset not found
        if( self::$tilesetFilename === false ) {  
            echo 'Error: Tileset not specified'.CR;
            return;
        }
        
        // map not found
        if( self::$mapFilename === false ) {
            echo 'Error: Map not specified'.CR;
            return;
        }

        // read map and tileset
        self::ReadMap(self::$mapFilename);
        self::ReadTileset(self::$tilesetFilename);

        if( self::$error === true ) {
            echo 'Error: '.self::$errorDetails;
            return;
        }
        
        // now output the spectrum data
        self::SaveSpectrumData();
    }

    private static function ReadMap($filename) {

        if(!file_exists($filename)) {

            self::$error = true;
            self::$errorDetails .= 'Map file not found. ';
            return;
        }

        $json = readfile($filename);
        
        self::$tileMap = json_decode($json);
    }

    private static function ReadTileset($filename) {

        if(!file_exists($filename)) {

            self::$error = true;
            self::$errorDetails .= 'Tileset file not found. ';
            return;
        }

        $json = readfile($filename);
        $data = json_decode($json);

        foreach($data as $tile) {
            Tile::AddTile($tile);
        }
    }

    private static function SaveAttributes() {
        
        foreach(self::$tileMap as $tileNum) {
            self::$paperMap[] = Tile::GetTilePaper($tileNum);
            self::$inkMap[] = Tile::GeTileInk($tileNum);
            self::$brightMap[] = Tile::GetTileBright($tileNum);
        }
    }

    private static function SaveSpectrumData() {

        // output tile numbers

        // output paper

        // output ink

        // output bright

        // save to file
    }
}

// read filenames from command line arguments
$options = getopt('m:t:o:', ['map:', 'tileset:', 'output:']);

// run
SpecScreenTool::Run($options);
