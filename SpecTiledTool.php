<?php
namespace ClebinGames\SpecTiledTool;

define('CR', "\n");

require("CliTools.php");
require("Attribute.php");
require("Tile.php");
require("Tileset.php");
require("Tilemap.php");
require("Graphics.php");
require("Sprite.php");

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
    
    // current output format
    public static $format = self::FORMAT_C;
    
    // naming
    public static $prefix = false;

    
    // filenames
    private static $spriteFilename = false;
    private static $maskFilename = false;
    private static $mapFilename = false;
    private static $tilesetFilename = false;
    private static $outputFolder = '.';
    private static $outputFilename = false;
    private static $graphicsFilename = false;
    private static $spriteWidth = false;

    // save game properties
    public static $saveSolidData = false;
    public static $saveLethalData = false;

    // add custom game properties to tiles
    public static $customProperties = [];
    
    // output
    private static $output = '';
    public static $saveScreensInOwnFile = true;
    public static $saveGameProperties = false;

    // errors
    private static $error = false;
    private static $errorDetails = [];

    /**
     * Run the tool
     */
    public static function Run($options)
    {
        self::OutputIntro();

        // no options set - ask questions
        if( sizeof($options) == 0 ) {
            
            self::$prefix = CliTools::GetAnswer('Naming prefix', 'tiles');
            self::$mapFilename = CliTools::GetAnswer('Map filename', 'map.tmj');
            self::$tilesetFilename = CliTools::GetAnswer('Tileset filename', 'tileset.tsj');
            self::$graphicsFilename = CliTools::GetAnswer('Tile graphics filename', 'tiles.gif');
            self::$outputFolder = CliTools::GetAnswer('Output folder?', './');
            self::$format = CliTools::GetAnswer('Which format?', 'c', ['c','asm']);
            self::$spriteWidth = intval(CliTools::GetAnswer('Sprite width in columns', 1));
        }
        // get options from command line arguments
        else {

            // prefix
            if( isset($options['prefix'])) {
                self::$prefix = $options['prefix'];
            }

            // tilemaps
            if( isset($options['map'])) {
                self::$mapFilename = $options['map'];
            }

            // tileset
            if( isset($options['tileset'])) {
                self::$tilesetFilename = $options['tileset'];
            }

            // graphics
            if( isset($options['graphics'])) {
                self::$graphicsFilename = $options['graphics'];
            }

            // format
            if( isset($options['format'])) {
                self::$format = $options['format'];
            }

            if( isset($options['outputfolder'])) {
                self::$outputFolder = $options['outputfolder'];
            }

            // sprite file
            if( isset($options['sprite'])) {
                self::$spriteFilename = $options['sprite'];

                if( isset($options['mask'])) {
                    self::$maskFilename = $options['mask'];
                }
            }

            // graphics
            if( isset($options['sprite-width'])) {
                self::$spriteWidth = intval($options['sprite-width']);
            }

        }

        self::$outputFolder = rtrim(self::$outputFolder, '/').'/';

        // read files
        self::ProcessTileset();
        self::ProcessScreens();
        self::ProcessSprite();
    }

    private static function ProcessTileset()
    {
        $file_output = '';
        
        $outputBaseFilename = self::$outputFolder;

        // output filename
        if( self::$prefix !== false ) {
            $outputBaseFilename .= self::$prefix.'-tileset';
        } else {
            $outputBaseFilename .= 'tileset';
        }
        
        // read tileset graphics
        if( self::$graphicsFilename !== false ) {

            $success = Graphics::ReadFile(self::$graphicsFilename);
            
            if( $success === true ) {
                // write graphics to file
                $file_output .= Graphics::GetCode();
            }
        }

        // tileset colours and properties
        if( self::$tilesetFilename !== false ) {

            $success = Tileset::ReadFile(self::$tilesetFilename);

            if( $success === true ) {        
                // write graphics to file
                $file_output .= Tileset::GetCode();
            }
        }

        // write data to file
        if( $file_output != '' ) {
            file_put_contents($outputBaseFilename.'.'.self::GetOutputFileExtension(), $file_output);
        }
    }

    private static function ProcessScreens()
    {
        // read map and tilset
        Tilemap::ReadFile(self::$mapFilename);
    
        if( self::$error === false ) {

            $outputBaseFilename = self::$outputFolder;

            // output filename
            if( self::$prefix !== false ) {
                $outputBaseFilename .= self::$prefix.'-screens';
            } else {
                $outputBaseFilename .= 'screens';
            }

            // write graphics to file
            if( self::$saveScreensInOwnFile ===  true ) {

                for($i=0;$i<Tilemap::GetNumScreens();$i++) {
                    file_put_contents($outputBaseFilename.'-'.$i.'.'.self::GetOutputFileExtension(), Tilemap::GetScreenCode($i));
                }
            }
            else {
                file_put_contents($outputBaseFilename.'.'.self::GetOutputFileExtension(), Tilemap::GetCode());
            }
        }
    }

    private static function ProcessSprite()
    {
        // read sprite
        if( self::$spriteFilename !== false ) {
            Sprite::ReadFiles(self::$spriteFilename, self::$maskFilename);
        
            if( self::$error === false ) {

                $outputBaseFilename = self::$outputFolder;

                // output filename
                if( self::$prefix !== false ) {
                    $outputBaseFilename .= self::$prefix.'-sprite';
                } else {
                    $outputBaseFilename .= 'sprite';
                }

                echo $outputBaseFilename;

                file_put_contents($outputBaseFilename.'.'.self::GetOutputFileExtension(), Sprite::GetCode());
            }
        }

        if( self::$error === true ) {
            echo 'Errors ('.sizeof(self::$errorDetails).'): '.implode('. ', self::$errorDetails);
            return false;
        }
    }

    /**
     * Get output file extension for the current format/language
     */
    public static function GetOutputFileExtension()
    {
        switch(self::$format) {            
            case 'c':
                return 'c';
                break;

            default:
                return 'asm';
        }
    }

    /**
     * Get current format/langauge
     */
    public static function GetFormat()
    {
        return self::$format;
    }

    /**
     * Get naming prefix
     */
    public static function GetPrefix()
    {
        return self::$prefix;
    }

    /**
     * Get sprite width
     */
    public static function getSpriteWidth()
    {
        return self::$spriteWidth;
    }
    
    /**
     * Return an array as a string in C format
     */
    public static function GetCArray($name, $values, $numbase = 10)
    {
        $str = 'const unsigned char '.$name.'['.sizeof($values).'] = {'.CR;
        
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

    /**
     * Return an array as a string in assembly format
     */
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

    /**
     * Output intro text on command line
     */
    public static function OutputIntro()
    {
        echo '****************************'.CR;
        echo '* Spectrum Screen Tool     *'.CR;
        echo '* Chris Owen 2022          *'.CR;
        echo '****************************'.CR;
    }

    /**
     * Add to errors list
     */
    public static function AddError($error)
    {
        self::$error = true;
        self::$errorDetails[] = ltrim($error, '.');
    }

    /**
     * Did an error occur?
     */
    public static function DidErrorOccur()
    {
        return self::$error;
    }
}

// read filenames from command line arguments
$options = getopt('', [
    'help::', 
    'prefix::', 
    'map::', 
    'tileset::', 
    'graphics::',
    'format::', 
    'sprite::', 
    'mask::', 
    'outputfolder::'
]);

// run
SpecTiledTool::Run($options);

echo CR;
