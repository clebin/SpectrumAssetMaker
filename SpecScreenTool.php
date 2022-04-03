<?php
namespace ClebinGames\SpecScreenTool;

define('CR', "\n");

require("CliTools.php");
require("Tile.php");
require("Tileset.php");
require("Tilemap.php");
require("Graphics.php");

class SpecScreenTool {
    
    // filenames
    private static $mapFilename = false;
    private static $tilesetFilename = false;
    private static $outputFilename = false;
    private static $graphicsFilename = false;

    // save game properties
    public static $saveSolidData = false;
    public static $saveLethalData = false;

    // add custom game properties to tiles
    public static $customProperties = [];
    
    // output
    private static $output = '';

    // errors
    private static $error = false;
    private static $errorDetails = [];

    public static function Run($options) {

        self::OutputIntro();

        // tilemaps
        if( isset($options['m'])) {
            self::$mapFilename = $options['m'];
        } else if( isset($options['map'])) {
            self::$mapFilename = $options['map'];
        } else {
            self::$mapFilename = CliTools::GetAnswer('Map filename', 'map.tmj');
        }

        // tileset
        if( isset($options['t'])) {
            self::$tilesetFilename = $options['t'];
        } else if( isset($options['tileset'])) {
            self::$tilesetFilename = $options['tileset'];
        } else {
            self::$tilesetFilename = CliTools::GetAnswer('Tileset filename', 'tileset.tsj');
        }

        // graphics
        if( isset($options['g'])) {
            self::$graphicsFilename = $options['g'];
        } else if( isset($options['graphics'])) {
            self::$graphicsFilename = $options['graphics'];
        } else {
            self::$graphicsFilename = CliTools::GetAnswer('Tile graphics filename', 'tiles.png');
        }

        // output file
        if( isset($options['o'])) {
            self::$outputFilename = $options['o'];
        } else if( isset($options['output'])) {
            self::$outputFilename = $options['output'];
        } else {
            self::$outputFilename = CliTools::GetAnswer('Output filename', 'tiles.asm');
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

        // game properties
        // self::$saveSolidData = CliTools::GetAnswerBoolean('Save solid block details?', false);
        // self::$saveLethalData = CliTools::GetAnswerBoolean('Save lethal block details?', false);
        
        // read graphics, map and tileset
        Graphics::ReadFile(self::$graphicsFilename);
        Tilemap::ReadFile(self::$mapFilename);
        Tileset::ReadFile(self::$tilesetFilename);

        if( self::$error === true ) {
            echo 'Errors ('.sizeof(self::$errorDetails).'): '.implode('. ', self::$errorDetails);
            return false;
        }
        
        // now output the spectrum data
        self::SaveSpectrumData();
    }

    public static function OutputIntro()
    {
        echo '****************************'.CR;
        echo '* Spectrum Screen Tool     *'.CR;
        echo '* Chris Owen 2022          *'.CR;
        echo '****************************'.CR;
    }

    public static function AddError($error)
    {
        self::$error = true;
        self::$errorDetails[] = ltrim($error, '.');
    }
}

// read filenames from command line arguments
$options = getopt('hmtgo', ['help', 'map', 'tileset', 'graphics', 'output']);

// run
SpecScreenTool::Run($options);

echo CR;
