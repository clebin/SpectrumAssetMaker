<?php
namespace ClebinGames\SpecScreenTool;

define('CR', "\n");

require("CliTools.php");
require("Tile.php");
require("Tileset.php");
require("Tilemap.php");
require("Graphics.php");

/**
 * Spectrum Screen Tool
 * Chris Owen 2022
 * 
 * Read Tiled map and tileset and save screen data for use on the Spectrum.
 * Load PNG/GIF graphics data and save as graphics data
 * 
 * Load multiple Tiled layers and save as individual screens
 * Add custom properites to attributes/tiles
 */
class SpecScreenTool
{
    // naming
    public static $prefix = 'tiles';
    
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

    public static function Run($options)
    {
        self::OutputIntro();

        // prefix
        if( isset($options['p'])) {
            self::$prefix = $options['p'];
        } else if( isset($options['prefix'])) {
            self::$prefix = $options['prefix'];
        } else {
            self::$prefix = CliTools::GetAnswer('Naming prefix', 'tiles');
        }
        
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
            self::$graphicsFilename = CliTools::GetAnswer('Tile graphics filename', 'tiles.gif');
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

        if( self::$error === false ) {
            // write graphics to file
            file_put_contents(self::$prefix.'-graphics.asm', Graphics::GetAsm());
        }
        
        Tileset::ReadFile(self::$tilesetFilename);
        Tilemap::ReadFile(self::$mapFilename);



        if( self::$error === true ) {
            echo 'Errors ('.sizeof(self::$errorDetails).'): '.implode('. ', self::$errorDetails);
            return false;
        }
        
        // now output the spectrum data
        //self::SaveSpectrumData();
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
$options = getopt('hpmtgo', ['help', 'prefix', 'map', 'tileset', 'graphics', 'output']);

// run
SpecScreenTool::Run($options);

echo CR;
