<?php
namespace ClebinGames\SpecTiledTool;

define('CR', "\n");

require("CliTools.php");
require("Attribute.php");
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
class SpecTiledTool
{
    // constants
    const FORMAT_ASM = 'asm';
    const FORMAT_C = 'c';
    const FORMAT_BASIC = 'basic';
    
    // current output format
    public static $format = self::FORMAT_ASM;
    
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
    public static $saveScreensInOwnFile = true;

    // errors
    private static $error = false;
    private static $errorDetails = [];

    public static function Run($options)
    {
        self::OutputIntro();

        // no options set - ask questions
        if( sizeof($options) == 0 ) {
            
            self::$prefix = CliTools::GetAnswer('Naming prefix', 'tiles');
            self::$mapFilename = CliTools::GetAnswer('Map filename', 'map.tmj');
            self::$tilesetFilename = CliTools::GetAnswer('Tileset filename', 'tileset.tsj');
            self::$graphicsFilename = CliTools::GetAnswer('Tile graphics filename', 'tiles.gif');
            Tilemap::$startLayer = CliTools::GetAnswer('Which layer to start?', 0);
            self::$format = CliTools::GetAnswer('Which format?', 'asm', ['basic','c']);

        } else {

            // prefix
            if( isset($options['p'])) {
                self::$prefix = $options['p'];
            } else if( isset($options['prefix'])) {
                self::$prefix = $options['prefix'];
            }

            // tilemaps
            if( isset($options['m'])) {
                self::$mapFilename = $options['m'];
            } else if( isset($options['map'])) {
                self::$mapFilename = $options['map'];
            } else {
                echo 'Error: Map not specified'.CR;
                return;
            }

            // tileset
            if( isset($options['t'])) {
                self::$tilesetFilename = $options['t'];
            } else if( isset($options['tileset'])) {
                self::$tilesetFilename = $options['tileset'];
            } else {
                echo 'Error: Tileset not specified'.CR;
                return;
            }

            // graphics
            if( isset($options['g'])) {
                self::$graphicsFilename = $options['g'];
            } else if( isset($options['graphics'])) {
                self::$graphicsFilename = $options['graphics'];
            }

            // start layer
            if( isset($options['s'])) {
                Tilemap::$startLayer = intval($options['s']);
            } else if( isset($options['start'])) {
                Tilemap::$startLayer = intval($options['start']);
            }

            // format
            if( isset($options['f']) ) {
                self::$format = $options['f'];
            } else if( isset($options['format'])) {
                self::$format = $options['format'];
            }

        }

        // game properties
        // self::$saveSolidData = CliTools::GetAnswerBoolean('Save solid block details?', false);
        // self::$saveLethalData = CliTools::GetAnswerBoolean('Save lethal block details?', false);

        // read graphics, map and tileset
        Graphics::ReadFile(self::$graphicsFilename);

        if( self::$error === false ) {

            // write graphics to file
            file_put_contents(self::$prefix.'-graphics.'.self::GetOutputFileExtension(), Graphics::GetCode());
        }
        
        Tileset::ReadFile(self::$tilesetFilename);
        Tilemap::ReadFile(self::$mapFilename);

        if( self::$error === false ) {
            // write graphics to file
            if( self::$saveScreensInOwnFile ===  true ) {

                for($i=0;$i<Tilemap::GetNumScreens();$i++) {
                    file_put_contents(self::$prefix.'-screens-'.$i.'.'.self::GetOutputFileExtension(), Tilemap::GetScreenCode($i));
                }
            }
            else {
                file_put_contents(self::$prefix.'-screens.'.self::GetOutputFileExtension(), Tilemap::GetCode());
            }
        }
        
        if( self::$error === true ) {
            echo 'Errors ('.sizeof(self::$errorDetails).'): '.implode('. ', self::$errorDetails);
            return false;
        }
    }

    public static function GetOutputFileExtension()
    {
        switch(self::$format) {
            case 'basic':
                return 'bas';
                break;
            
            case 'c':
                return 'c';
                break;

            default:
                return 'asm';
        }
    }

    public static function GetFormat()
    {
        return self::$format;
    }

    public static function GetPrefix()
    {
        return self::$prefix;
    }

    public static function GetCArray($name, $values, $numbase = 10)
    {
        $str = 'const uchar '.$name.'[] = {'.CR;
        
        // tile numbers
        $count = 0;
        foreach($values as $val) {

            if( $count > 0 ) {
                $str .= ',';
                if( $count % 8 == 0 ) {
                    $str .= CR;
                }
            }

            // convert to numbers to hex
            switch( $numbase ) {

                // binary
                case 2:
                    $str .= '0x'.dechex(bindec($val));
                break;
                
                // decimal
                case 10:
                    $str .= '0x'.dechex($val);
                break;

                // hex
                case 15:
                    $str .= '0x'.$val;
            }

            $count++;
        }

        $str .= CR.'};'.CR.CR;

        return $str;
    }

    public static function GetBasicArray($name, $values, $numbase = 10)
    {
        $str = 'Dim '.$name.'('.(sizeof($values)-1).') as uByte => { _'.CR;
        
        $count = 0;
        foreach($values as $val) {

            if( $count > 0 ) {
                $str .= ',';
                if( $count % 32 == 0 ) {
                    $str .= ' _'.CR;
                }
            }

            // convert to numbers to decimal
            switch( $numbase ) {

                // binary
                case 2:
                    $str .= bindec($val);
                break;
                
                // decimal
                case 10:
                    $str .= $val;
                break;

                // hex
                case 15:
                    $str .= hexdec($val);
            }

            $count++;
        }
        $str .= ' _'.CR.'}'.CR.CR;

        return $str;
    }

    public static function GetAsmArray($name, $values, $numbase = 10, $length = false)
    {
        // output paper/ink/bright/flash
        $str = CR.'._'.$name;
        
        $count = 0;
        foreach($values as $val) {

            if( $count % 4 == 0 ) {
                $str .= CR.'defb ';
            } else {
                $str .= ', ';
            }

            // convert to numbers to binary
            switch( $numbase ) {

                // binary
                case 2:
                    // do nothing
                break;
                
                // decimal
                case 10:
                    $val = decbin($val);
                break;

                // hex
                case 15:
                    $val = decbin(hexdec($val));
            }

            // pad binary string
            if( $length !== false ) {
                $val = str_pad( $val, $length, '0', STR_PAD_LEFT );
            }
            
            $str .= '@'.$val;
            
            $count++;
        }
        return $str;
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
$options = getopt('h::p::m::t::g::s::f::', ['help::', 'prefix::', 'map::', 'tileset::', 'graphics::', 'start::','format::']);

// run
SpecTiledTool::Run($options);

echo CR;
