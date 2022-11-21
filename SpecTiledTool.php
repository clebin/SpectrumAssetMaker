<?php

namespace ClebinGames\SpecTiledTool;

define('CR', "\n");

require("CliTools.php");
require("Attribute.php");
require("BlankData.php");
require("Tile.php");
require("Tilemap.php");
require("Tileset.php");
require("Tilemaps.php");
require("Graphics.php");
require("Sprite.php");
require("ObjectTypes.php");
require("ObjectMap.php");
require("GameObject.php");

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
    public static $formatsSupported = ['asm', 'c'];
    public static $format = self::FORMAT_C;

    public static $namingConventionsSupported = ['camelcase', 'underscores', 'titlecase'];
    public static $namingConvention = self::NAMING_CAMELCASE;

    // naming
    public static $name = false;
    public static $useLayerNames = false;
    public static $replaceFlashWithSolid = false;

    // compression
    public static $compressionSupported = ['rle'];
    public static $compression = false;

    // input filenames
    private static $spriteFilename = false;
    private static $maskFilename = false;
    private static $mapFilename = false;
    private static $tilesetFilename = false;
    private static $graphicsFilename = false;

    // tilemap layers
    private static $ignoreHiddenLayers = false;
    private static $layerType = 'all';
    private static $layerTypesSupported = ['all', 'objectgroup', 'tilelayer'];

    // blank data
    private static $blankDataSize = 0;

    // object types
    private static $objectTypesFilename = false;
    private static $objectCustomPropertiesFilename = false;

    // more settngs
    private static $outputFolder = '.';
    private static $addDimensions = false;
    private static $outputFilename = false;
    private static $spriteWidth = false;
    private static $createBinariesLst = false;

    // assembly section
    public static $section = 'rodata_user';

    // save game properties
    public static $saveSolidData = false;
    public static $saveLethalData = false;

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
        if (sizeof($options) == 0) {
            self::SetupWithUserPrompts();
        }
        // get options from command line arguments
        else {
            self::SetupWithArgs($options);
        }

        // is format supported?
        if (!in_array(self::$format, self::$formatsSupported)) {
            echo 'Error: Format not supported.' . CR;
            return false;
        }

        if (self::$compression !== false && !in_array(self::$compression, self::$compressionSupported)) {
            echo 'Error: Compression type not supported.' . CR;
            return false;
        }

        // set output folder
        self::$outputFolder = rtrim(self::$outputFolder, '/') . '/';

        // tileset colours and properties
        if (self::$tilesetFilename !== false) {
            Tileset::Process(self::$tilesetFilename);
        }

        // process tileset graphics
        if (self::$graphicsFilename !== false) {
            Graphics::Process(self::$graphicsFilename);
        }

        // blank data
        if (self::$blankDataSize > 0) {
            BlankData::Process(self::$blankDataSize);
        }

        // process object maps
        if (self::$objectTypesFilename !== false) {

            $success = ObjectTypes::Process(self::$objectTypesFilename);

            // quit before errors
            if ($success === false) {
                return false;
            }
        }

        // process tilemaps
        if (self::$mapFilename !== false) {

            // process tilemaps
            Tilemaps::Process(self::$mapFilename);

            // save binaries.lst
            if (SpecTiledTool::$createBinariesLst === true) {
                SpecTiledTool::ProcessBinariesLst();
            }
        }

        // process sprite
        if (self::$spriteFilename !== false) {
            Sprite::Process(self::$spriteFilename, self::$maskFilename);
        }

        // display errors
        if (self::$error === true) {
            echo 'Errors (' . sizeof(self::$errorDetails) . '): ' . implode('. ', self::$errorDetails);
            return false;
        }
    }

    /**
     * Set up the tool by prompting the user to answer questions
     */
    private static function SetupWithUserPrompts()
    {
        // naming
        self::$name = CliTools::GetAnswer('Name for files and variables', '');

        // mode - map or sprite
        $mode = CliTools::GetAnswer('Which mode?', 'map', ['map', 'sprite']);

        // tilemap
        if ($mode == 'map') {
            self::$mapFilename = CliTools::GetAnswer('Map filename', 'map.tmj');
            self::$tilesetFilename = CliTools::GetAnswer('Tileset filename', 'tileset.tsj');
            self::$graphicsFilename = CliTools::GetAnswer('Tile graphics filename', 'tiles.gif');
            self::$addDimensions = CliTools::GetAnswerBoolean('Add tilemap dimensions?');
        }
        // sprite
        else {
            self::$spriteFilename = CliTools::GetAnswer('Sprite filename', '');
            self::$maskFilename = CliTools::GetAnswer('Mask filename', str_replace('.gif', '-mask.gif', self::$spriteFilename));
            self::$spriteWidth = CliTools::GetAnswer('Sprite width in columns', 2);
        }

        // blank data

        // output foloder
        self::$outputFolder = CliTools::GetAnswer('Output folder?', './');

        // compression
        self::$compression = CliTools::GetAnswer('Use compression', 'none', array_merge(['none'], self::$compressionSupported));
        if (self::$compression == 'none') {
            self::$compression = false;
        }

        // format
        self::$format = CliTools::GetAnswer('Output format', 'c', self::$formatsSupported);
        if (self::$format == 'asm') {
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
        if (isset($options['name'])) {
            self::$name = $options['name'];
        }

        // use tilemap layer names
        if (isset($options['use-layer-names'])) {
            self::$useLayerNames = true;
        }

        // tilemaps
        if (isset($options['map'])) {
            self::$mapFilename = $options['map'];
        }

        // createbinaries.lst file
        if (isset($options['create-binaries-lst'])) {
            self::$createBinariesLst = true;
        }

        // add dimensions
        if (isset($options['add-dimensions'])) {
            self::$addDimensions = true;
        }

        // layer type
        if (isset($options['layer-type']) && in_array($options['layer-type'], self::$layerTypesSupported)) {
            self::$layerType = $options['layer-type'];
        }

        // ignore hidden layers
        if (isset($options['ignore-hidden-layers'])) {
            self::$ignoreHiddenLayers = true;
        }
        // tileset
        if (isset($options['tileset'])) {
            self::$tilesetFilename = $options['tileset'];
        }

        // replace flash bit with solid
        if (isset($options['replace-flash-with-solid'])) {
            self::$replaceFlashWithSolid = true;
        }

        // graphics
        if (isset($options['graphics'])) {
            self::$graphicsFilename = $options['graphics'];
        }

        // blank data
        if (isset($options['blank-data'])) {
            self::$blankDataSize = $options['blank-data'];
        }

        // object types
        if (isset($options['object-types'])) {
            self::$objectTypesFilename = $options['object-types'];
        }

        // object custom properties
        if (isset($options['object-properties'])) {
            self::$objectCustomPropertiesFilename = $options['object-properties'];
        }

        // format
        if (isset($options['format'])) {
            self::$format = $options['format'];
        }

        // output folder
        if (isset($options['output-folder'])) {
            self::$outputFolder = $options['output-folder'];
        }

        // naming
        if (isset($options['naming'])) {
            self::$namingConvention = $options['naming'];
        }

        // sprite file
        if (isset($options['sprite'])) {
            self::$spriteFilename = $options['sprite'];

            if (isset($options['mask'])) {
                self::$maskFilename = $options['mask'];
            }

            if (isset($options['sprite-width'])) {
                self::$spriteWidth = intval($options['sprite-width']);
            }
        }

        // section
        if (isset($options['section'])) {
            self::$section = $options['section'];
        }

        // compression
        if (isset($options['compression'])) {
            self::$compression = $options['compression'];
        }
    }

    /**
     * Process and save binaries.lst file (only for screens data)
     */
    private static function ProcessBinariesLst()
    {
        $strBinaries = '';
        if (self::$tilesetFilename !== false) {
            $strBinaries = Tileset::GetBinariesLst() . CR;
        }
        $strBinaries .= Tilemaps::GetBinariesLst();

        file_put_contents(self::$outputFolder . 'binaries.lst', $strBinaries);
    }

    /**
     * Returns the output folder
     */
    public static function GetOutputFolder()
    {
        return self::$outputFolder;
    }

    /**
     * Return types of layers being processed
     */
    public static function GetLayerType()
    {
        return self::$layerType;
    }

    /**
     * Are we ignoring hidden layers?
     */
    public static function GetIgnoreHiddenLayers()
    {
        return self::$ignoreHiddenLayers;
    }

    /**
     * Get output file extension for the current format/language
     */
    public static function GetOutputFileExtension()
    {
        switch (self::$format) {
            case 'c':
                return 'c';
                break;

            default:
                return 'asm';
        }
    }

    /**
     * Get output filename using a suffix
     */
    public static function GetOutputFilename($suffix = false)
    {
        $outputFilename = SpecTiledTool::$outputFolder;

        // output filename
        if (self::$name !== false) {
            $outputFilename .= SpecTiledTool::GetConvertedFilename(self::$name);
            if ($suffix !== false) {
                $outputFilename .= '-' . $suffix;
            }
        } else if ($suffix !== false) {
            $outputFilename .= $suffix;
        } else {
            $outputFilename .= 'data';
        }

        $outputFilename .= '.' . SpecTiledTool::GetOutputFileExtension();

        return $outputFilename;
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
     * Get name
     */
    public static function GetName()
    {
        return self::$name;
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
        if (Tileset::$large_tileset === true) {
            $str = 'const uint16_t ' . $name . '[' . sizeof($values) . '] = {' . CR;
        } else {
            $str = 'const uint8_t ' . $name . '[' . sizeof($values) . '] = {' . CR;
        }

        // tile numbers
        $count = 0;
        foreach ($values as $val) {

            if ($count > 0) {
                $str .= ',';
                if ($count % 8 == 0) {
                    $str .= CR;
                }
            }

            // convert to numbers to hex
            switch ($numbase) {

                    // binary
                case 2:
                    $str .= '0x' . dechex(bindec($val));
                    break;

                    // decimal
                case 10:
                    $str .= '0x' . dechex($val);
                    break;

                    // hex
                case 15:
                    $str .= '0x' . $val;
            }

            $count++;
        }

        $str .= CR . '};' . CR . CR;

        return $str;
    }

    /**
     * Return an array as a string in assembly format
     */
    public static function GetAsmArray($name, $values, $numbase = 10, $length = false, $public = true)
    {
        $str = '';

        if ($public === true) {
            $str .= CR . 'PUBLIC _' . $name . CR;
        }

        // output paper/ink/bright/flash
        $str .= CR . '._' . $name;

        $count = 0;
        foreach ($values as $val) {

            if ($count % 4 == 0) {
                $str .= CR . 'defb ';
            } else {
                $str .= ', ';
            }

            // convert to numbers to binary
            switch ($numbase) {

                    // binary
                case 2:
                    // do nothing
                    break;

                    // decimal
                case 10:
                    $val = decbin($val);
                    break;

                    // hex
                case 16:
                    $val = decbin(hexdec($val));
            }

            // pad binary string
            if ($length !== false) {
                $val = str_pad($val, $length, '0', STR_PAD_LEFT);
            }

            $str .= '@' . $val;

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
        for ($i = 0; $i < sizeof($input); $i++) {

            $count = 1;
            while ($i < sizeof($input) - 1 && $input[$i] == $input[$i + 1] && $count < 256) {
                $count++;
                $i++;
            }
            $output[] = $input[$i];
            $output[] = $count;
        }

        $inputSize = sizeof($input);
        $outputSize = sizeof($output);

        // record array length
        if ($add_length === true) {
            $bin = str_pad(decbin($outputSize), 16, '0', STR_PAD_LEFT);

            array_unshift($output, bindec(substr($bin, -8)));
            array_unshift($output, bindec(substr($bin, 0, 8)));
        }

        echo 'Compressed ' . ($name !== false ? $name : 'array') . ': ' . $inputSize . 'b -> ' . $outputSize . 'b, saved ' . round((($inputSize - $outputSize) / $inputSize) * 100, 1) . '%' . CR;

        return $output;
    }

    /**
     * Get C code for an array of pointers
     */
    public static function GetPointerArrayC($arrayName, $itemsBaseName, $size = 0)
    {
        $str = '';

        // tile number arrays
        $str .= 'const unsigned char *' . $arrayName . '[' . $size . '] = {';

        for ($i = 0; $i < $size; $i++) {
            if ($i > 0) {
                $str .= ', ';
            }
            $str .= $itemsBaseName . $i;
        }
        $str .= '};' . CR;

        return $str;
    }

    /**
     * Convert a regular name into a camel-case variable name to be used in code
     */
    public static function GetConvertedCodeName($source_name)
    {
        switch (self::$namingConvention) {
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
     * Convert a regular name to constant
     */
    public static function GetConvertedConstantName($source_name)
    {
        return strtoupper(self::GetConvertedCodeNameUnderscores($source_name));
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
        return lcfirst(implode('', array_map('ucfirst', explode(' ', str_replace('-', ' ', $source_name)))));
    }

    /**
     * Convert a regular name to title-case
     */
    public static function GetConvertedCodeNameTitleCase($source_name)
    {
        return implode('', array_map('ucfirst', explode(' ', str_replace('-', ' ', $source_name))));
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
        echo '** Spectrum Tiled Tool v' . self::VERSION . ' - Chris Owen 2022 **' . CR . CR;
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
     * Adding dimensions?
     */
    public static function GetAddDimensions()
    {
        return self::$addDimensions;
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
    'name::',
    'map::',
    'blank-data::',
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
    'naming::',
    'add-dimensions::',
    'object-types::',
    'object-properties::',
    'layer-type::',
    'ignore-hidden-layers::'
]);

// run
SpecTiledTool::Run($options);

echo CR;
