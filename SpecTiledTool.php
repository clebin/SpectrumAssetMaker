<?php
namespace ClebinGames\SpecTiledTool;

define('CR', "\n");

require("CliTools.php");
require("Attribute.php");
require("Tile.php");
require("Screen.php");
require("Tileset.php");
require("Tilemaps.php");
require("TilesetGraphics.php");
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
    const VERSION = '0.4';

    // constants
    const FORMAT_ASM = 'asm';
    const FORMAT_C = 'c';
    const NAMING_CAMELCASE = 'camelcase';
    const NAMING_UNDERSCORES = 'underscores';
    
    // current output format
    public static $formatsSupported = ['asm','c'];
    public static $format = self::FORMAT_C;

    public static $namingConventionsSupported = ['camelcase','underscores','titlecase'];
    public static $namingConvention = self::NAMING_CAMELCASE;
    
    // naming
    public static $prefix = false;
    public static $useLayerNames = false;
    public static $replaceFlashWithSolid = false;

    // compression
    public static $compressionSupported = ['rle'];
    public static $compression = false;

    // filenames
    private static $spriteFilename = false;
    private static $maskFilename = false;
    private static $mapFilename = false;
    private static $tilesetFilename = false;
    private static $outputFolder = '.';
    private static $outputFilename = false;
    private static $graphicsFilename = false;
    private static $spriteWidth = false;
    private static $createBinariesLst = false;

    // assembly section
    public static $section = 'rodata_user';
    
    // save game properties
    public static $saveSolidData = false;
    public static $saveLethalData = false;
    
    // add custom game properties to tiles
    public static $customProperties = [];
    
    // output
    private static $output = '';
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
            self::SetupWithUserPrompts();
        }
        // get options from command line arguments
        else {
            self::SetupWithArgs($options);
        }

        // is format supported?
        if( !in_array(self::$format, self::$formatsSupported) ) {
            echo 'Error: Format not supported.'.CR;
            return false;
        }

        if( self::$compression !== false && !in_array(self::$compression, self::$compressionSupported)) {
            echo 'Error: Compression type not supported.'.CR;
            return false;
        }
        
        // set output folder
        self::$outputFolder = rtrim(self::$outputFolder, '/').'/';

        // process files
        self::ProcessTileset();
        self::ProcessScreens();
        self::ProcessSprite();
    }

    /**
     * Set up the tool by prompting the user to answer questions
     */
    private static function SetupWithUserPrompts()
    {
        // naming prefix
        self::$prefix = CliTools::GetAnswer('Naming prefix', 'tiles');

        // mode - map or sprite
        $mode = CliTools::GetAnswer('Which mode?', 'map', ['map','sprite']);
        
        // tilemap
        if( $mode == 'map' ) {
            self::$mapFilename = CliTools::GetAnswer('Map filename', 'map.tmj');
            self::$tilesetFilename = CliTools::GetAnswer('Tileset filename', 'tileset.tsj');
            self::$graphicsFilename = CliTools::GetAnswer('Tile graphics filename', 'tiles.gif');
        }
        // sprite
        else {
            self::$spriteFilename = CliTools::GetAnswer('Sprite filename', '');
            self::$maskFilename = CliTools::GetAnswer('Mask filename', str_replace('.gif', '-mask.gif', self::$spriteFilename));
            self::$spriteWidth = CliTools::GetAnswer('Sprite width in columns', 2);   
        }

        // output foloder
        self::$outputFolder = CliTools::GetAnswer('Output folder?', './');

        // compression
        self::$compression = CliTools::GetAnswer('Use compression', 'none', array_merge(['none'], self::$compressionSupported));
        if( self::$compression == 'none' ) {
            self::$compression = false;
        }

        // format
        self::$format = CliTools::GetAnswer('Output format', 'c', self::$formatsSupported);
        if(self::$format == 'asm') {
            self::$section = CliTools::GetAnswer('Asssembly section', 'rodata_user');
        }

        // naming
        self::$namingConvention = CliTools::GetAnswer('Naming convention', 'camelcase', self::$namingConventionsSupported);
    }

    /**
     * Set up the tool using parameters passed on the command line
     */
    private static function SetupWithArgs($options)
    {
        // prefix
        if( isset($options['prefix'])) {
            self::$prefix = $options['prefix'];
        }

        if( isset($options['use-layer-names'])) {
            self::$useLayerNames = true;
        }

        if( isset($options['create-binaries-lst'])) {
            self::$createBinariesLst = true;
        }

        // tilemaps
        if( isset($options['map'])) {
            self::$mapFilename = $options['map'];
        }

        // tileset
        if( isset($options['tileset'])) {
            self::$tilesetFilename = $options['tileset'];
        }

        // replace flash bit with solid
        if( isset($options['replace-flash-with-solid']) ) {
            self::$replaceFlashWithSolid = true;
        }

        // graphics
        if( isset($options['graphics'])) {
            self::$graphicsFilename = $options['graphics'];
        }

        // format
        if( isset($options['format'])) {
            self::$format = $options['format'];
        }

        // output folder
        if( isset($options['output-folder'])) {
            self::$outputFolder = $options['output-folder'];
        }

        // naming
        if( isset($options['naming'])) {
            self::$namingConvention = $options['naming'];
        }

        // sprite file
        if( isset($options['sprite'])) {
            self::$spriteFilename = $options['sprite'];

            if( isset($options['mask'])) {
                self::$maskFilename = $options['mask'];
            }
            
            if( isset($options['sprite-width'])) {
                self::$spriteWidth = intval($options['sprite-width']);
            }
        }

        // section
        if( isset($options['section'])) {
            self::$section = intval($options['section']);
        }

        // compression
        if( isset($options['compression']) ) {
            self::$compression = $options['compression'];
        }
    }

    /**
     * Process and save tileset data
     */
    private static function ProcessTileset()
    {
        $file_output = '';
        
        // read tileset graphics
        if( self::$graphicsFilename !== false ) {

            $success = TilesetGraphics::ReadFile(self::$graphicsFilename);
            
            if( $success === true ) {
                // write graphics to file
                $file_output .= TilesetGraphics::GetCode();
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

            // prepend memory section
            if( self::$format == 'asm') {
                $file_output = 'SECTION '.self::$section.CR.CR.$file_output;
            }

            file_put_contents(Tileset::GetOutputFilepath(), $file_output);
        }
    }

    /**
     * Process and save tilemap data
     */
    private static function ProcessScreens()
    {
        // read map and tilset
        Tilemaps::ReadFile(self::$mapFilename);
    
        if( self::$error === false ) {

            // write tilemaps to files
            $count = 0;
            foreach(Tilemaps::$screens as $screen) {
                file_put_contents($screen->GetOutputFilepath(), $screen->GetCode());
                $count++;
            }

            // save binaries.lst
            if( self::$createBinariesLst === true ) {
                self::ProcessBinariesLst();
            }
        }
    }

    /**
     * Process and save binaries.lst file (only for screens data)
     */
    private static function ProcessBinariesLst()
    {
        $strBinaries = '';
        if( self::$tilesetFilename !== false ) { 
            $strBinaries = Tileset::GetBinariesLst().CR;
        }
        $strBinaries .= Tilemaps::GetBinariesLst();

        file_put_contents(self::$outputFolder.'binaries.lst', $strBinaries);
    }

    /**
     * Process sprite file and mask
     */
    private static function ProcessSprite()
    {
        // read sprite
        if( self::$spriteFilename !== false ) {
            Sprite::ReadFiles(self::$spriteFilename, self::$maskFilename);
        
            if( self::$error === false ) {

                $outputFilename = self::$outputFolder;

                // output filename
                if( self::$prefix !== false ) {
                    $outputFilename .= SpecTiledTool::GetConvertedFilename(self::$prefix).'-sprite';
                } else {
                    $outputFilename .= 'sprite';
                }
                $outputFilename .= '.'.self::GetOutputFileExtension();

                file_put_contents($outputFilename, Sprite::GetCode());
            }
        }

        if( self::$error === true ) {
            echo 'Errors ('.sizeof(self::$errorDetails).'): '.implode('. ', self::$errorDetails);
            return false;
        }
    }

    /**
     * Returns the output folder
     */
    public static function GetOutputFolder()
    {
        return self::$outputFolder;
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
     * Return the code section to put data into
     */
    public static function GetCodeSection()
    {
        return self::$section;
    }

    /**
     * Get naming prefix
     */
    public static function GetPrefix()
    {
        return self::$prefix;
    }

    /**
     * Are we using layer names for code naming?
     */
    public static function UseLayerNames()
    {
        return self::$useLayerNames;
    }

    /**
     * Are we replacing the flash bit with solid bit?
     */
    public static function ReplaceFlashWithSolid()
    {
        return self::$replaceFlashWithSolid;
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
        if( Tileset::$large_tileset === true ) {
            $str = 'const uint16_t '.$name.'['.sizeof($values).'] = {'.CR;
        } else {
            $str = 'const uint8_t '.$name.'['.sizeof($values).'] = {'.CR;
        }
        
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
    public static function GetAsmArray($name, $values, $numbase = 10, $length = false, $public = true)
    {
        $str = '';

        if( $public === true ) {
            $str .= CR.'PUBLIC _'.$name.CR;
        }

        // output paper/ink/bright/flash
        $str .= CR.'._'.$name;
        
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
     * Compress data array using run-length encoding
     */
    public static function CompressArrayRLE($name, $input, $add_length = true)
    {
        $output = [];

        // add array data
        for($i=0;$i<sizeof($input);$i++) {

            $count = 1;
            while($i<sizeof($input)-1 && $input[$i] == $input[$i+1] && $count < 256) {
                $count++;
                $i++;
            }
            $output[] = $input[$i];
            $output[] = $count;
        }

        $inputSize = sizeof($input);
        $outputSize = sizeof($output);

        // record array length
        if( $add_length === true ) {
            $bin = str_pad( decbin($outputSize), 16, '0', STR_PAD_LEFT );

            array_unshift($output, bindec(substr($bin, -8)));
            array_unshift($output, bindec(substr($bin, 0, 8)));
        }
        
        echo 'Compressed '.($name !== false ? $name : 'array').': '.$inputSize.'b -> '.$outputSize.'b, saved '.round( (($inputSize-$outputSize)/$inputSize)*100, 1).'%'.CR;

        return $output;
    }

    /**
     * Get C code for an array of pointers
     */
    public static function GetPointerArrayC($arrayName, $itemsBaseName, $size = 0)
    {
        $str = '';

        // tile number arrays
        $str .= 'const unsigned char *'.$arrayName.'['.$size.'] = {';
        
        for($i=0;$i<$size;$i++) {
            if($i>0) {
                $str .= ', ';
            }
            $str .= $itemsBaseName.$i;
        }
        $str .= '};'.CR;
    
        return $str;
    }

    /**
     * Convert a regular name into a camel-case variable name to be used in code
     */
    public static function GetConvertedCodeName($source_name)
    {
        switch( self::$namingConvention ) {
            case 'underscores':
                return self::GetConvertedCodeNameUnderscores($source_name);
                break;
            case 'titlecase';
                return self::GetConvertedCodeNameTitleCase($source_name);
            default:
                return self::GetConvertedCodeNameCamelCase($source_name);
                break;
        }
    }

    /** 
     * Convert a regular name to use underscores 
     */
    public static function GetConvertedCodeNameUnderscores($source_name)
    {
        return strtolower(str_replace(['-', ' '], '_', $source_name));
    }

    /**
     * Convert a regular name to camel-case
     */
    public static function GetConvertedCodeNameCamelCase($source_name)
    {
        return lcfirst( implode('', array_map('ucfirst', explode(' ',str_replace('-', ' ',$source_name)))));
    }

    /**
     * Convert a regular name to title-case
     */
    public static function GetConvertedCodeNameTitleCase($source_name)
    {
        return implode('', array_map('ucfirst', explode(' ',str_replace('-', ' ',$source_name))));
    }
    
    /**
     * Convert regular name to a filename format
     */
    public static function GetConvertedFilename($source_name)
    {
        return strtolower(str_replace(' ', '-', $source_name));
    }

    /**
     * Output intro text on command line
     */
    public static function OutputIntro()
    {
        echo '** Spectrum Tiled Tool v'.self::VERSION.' - Chris Owen 2022 **'.CR.CR;
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
    'section::', 
    'compression::',
    'output-folder::',
    'use-layer-names::',
    'create-binaries-lst::', 
    'replace-flash-with-solid::',
    'naming::'
]);

// run
SpecTiledTool::Run($options);

echo CR;
