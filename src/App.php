<?php

namespace ClebinGames\SpectrumAssetMaker;

use \ClebinGames\SpectrumAssetMaker\Datatypes\BlankData;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tilemap;
use \ClebinGames\SpectrumAssetMaker\Datatypes\TilemapXML;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tileset;
use \ClebinGames\SpectrumAssetMaker\Datatypes\TilesetXML;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Graphics;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Sprite;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Text;

/**
 * Spectrum Asset Maker
 * Chris Owen 2022
 * 
 * Read Tiled map and tileset and save screen data for use on the Spectrum.
 * Load PNG/GIF graphics data and save as graphics data
 * 
 * Load multiple Tiled layers and save as individual screens
 * Add custom properites to attributes/tiles
 */
class App
{
    const VERSION = '0.10';

    // constants
    const FORMAT_ASM = 'asm';
    const FORMAT_C = 'c';
    const NAMING_CAMELCASE = 'camelcase';
    const NAMING_UNDERSCORES = 'underscores';

    // colour constants
    const COLOUR_BLACK = 'black';
    const COLOUR_BLUE = 'blue';
    const COLOUR_RED = 'red';
    const COLOUR_MAGENTA = 'magenta';
    const COLOUR_GREEN = 'green';
    const COLOUR_CYAN = 'cyan';
    const COLOUR_YELLOW = 'yellow';
    const COLOUR_WHITE = 'white';

    // speccy rgb colour equivalents
    public static $rgbColours = [
        App::COLOUR_BLACK => [0, 0, 0],
        App::COLOUR_BLUE => [0, 0, 255],
        App::COLOUR_RED => [255, 0, 0],
        App::COLOUR_MAGENTA => [255, 0, 255],
        App::COLOUR_GREEN => [0, 255, 0],
        App::COLOUR_CYAN => [0, 255, 255],
        App::COLOUR_YELLOW => [255, 255, 0],
        App::COLOUR_WHITE => [255, 255, 255]
    ];

    // set graphics paper colourcolourIsPaper
    public static $paperColour = self::COLOUR_WHITE;

    // current output format
    public static $formatsSupported = ['asm', 'c'];
    public static $format = self::FORMAT_ASM;

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

    // tileset properties
    public static $forceTilesetProperties = false;

    // text
    private static $textFilename = false;
    private static $stringDelimiter = CR;

    // tilemap layers
    private static $ignoreHiddenLayers = false;
    private static $layerType = 'all';
    private static $layerTypesSupported = ['all', 'objectgroup', 'tilelayer'];
    public static $generatePaths = false;

    // blank data
    private static $blankDataSize = 0;

    // object types
    private static $objectTypesFilename = false;

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
        self::SetupWithArgs($options);

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

            // xml format
            if (strpos(self::$tilesetFilename, '.tsx') !== false) {
                $tileset = new TilesetXML(self::$name);
            }
            // json format
            else {
                $tileset = new Tileset(self::$name);
            }
            $tileset->ProcessFile(self::$tilesetFilename);
        }

        // process tileset graphics
        if (self::$graphicsFilename !== false) {
            $graphics = new Graphics(self::$name);
            $graphics->ProcessFile(self::$graphicsFilename);
        }

        // blank data
        if (self::$textFilename !== false) {
            $datatype = new Text(self::$name);
            $datatype->ProcessFile(self::$textFilename);
        }

        // blank data
        if (self::$blankDataSize > 0) {
            $datatype = new BlankData(self::$name);
            $datatype->Process(self::$blankDataSize);
        }

        // process object maps
        if (self::$objectTypesFilename !== false) {

            $success = ObjectTypes::ProcessFile(self::$objectTypesFilename);

            // quit before errors
            if ($success === false) {
                return false;
            }
        }

        // process tilemaps
        if (self::$mapFilename !== false) {

            // xml tilemap
            if (strpos(self::$mapFilename, '.tmx') !== false) {
                $tilemap = new TilemapXML(self::$name);
            }
            // json tilemap
            else {
                $tilemap = new Tilemap(self::$name);
            }

            $tilemap->ProcessFile(self::$mapFilename);

            // save binaries.lst
            if (App::$createBinariesLst === true) {
                App::ProcessBinariesLst();
            }
        }

        // process sprite
        if (self::$spriteFilename !== false) {
            $sprite = new Sprite(self::$name);
            $sprite->Process(self::$spriteFilename, self::$maskFilename);
        }

        // display errors
        if (self::$error === true) {
            echo 'Errors (' . sizeof(self::$errorDetails) . '): ' . implode('. ', self::$errorDetails);
            return false;
        }
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

        // generate paths from tilemap
        if (isset($options['generate-paths'])) {
            self::$generatePaths = true;
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

        // always add tileset properties array
        if (isset($options['add-tileset-properties'])) {
            self::$forceTilesetProperties = true;
        }

        // text
        if (isset($options['text'])) {
            self::$textFilename = $options['text'];

            if (isset($options['string-delimiter'])) {
                self::$stringDelimiter = intval($options['string-delimiter']);
            }
        }

        // replace flash bit with solid
        if (isset($options['replace-flash-with-solid'])) {
            self::$replaceFlashWithSolid = true;
        }

        // graphics
        if (isset($options['graphics'])) {
            self::$graphicsFilename = $options['graphics'];
        }

        // paper colour
        if (isset($options['paper-colour'])) {
            switch ($options['paper-colour']) {
                case 'black':
                    self::$paperColour = self::COLOUR_BLACK;
                    break;
                case 'blue':
                    self::$paperColour = self::COLOUR_BLUE;
                    break;
                case 'red':
                    self::$paperColour = self::COLOUR_RED;
                    break;
                case 'magenta':
                    self::$paperColour = self::COLOUR_MAGENTA;
                    break;
                case 'green':
                    self::$paperColour = self::COLOUR_GREEN;
                    break;
                case 'cyan':
                    self::$paperColour = self::COLOUR_CYAN;
                    break;
                case 'yellow':
                    self::$paperColour = self::COLOUR_YELLOW;
                    break;
                case 'white':
                    self::$paperColour = self::COLOUR_WHITE;
            }
        }

        // blank data
        if (isset($options['blank-data'])) {
            self::$blankDataSize = $options['blank-data'];
        }

        // object types
        if (isset($options['object-types'])) {
            self::$objectTypesFilename = $options['object-types'];
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
     * Check if rgb colour matches paper colour
     */
    public static function colourIsPaper($rgb, $filetype = 'gif')
    {
        // get rgb values
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        // gif
        if ($filetype == 'gif') {
            // pure black counts as ink
            if ($r == 0 && $g == 0 && $b == 0) {
                return false;
            }
            // anything else is paper
            else {
                return true;
            }
        }
        // png file
        else {

            // echo $r . '-' . $g . '-' . $b . CR;
            $paper = self::$rgbColours[self::$paperColour];

            if ($r != $paper[0] || $g != $paper[1] || $b != $paper[2]) {
                return false;
            } else {
                return true;
            }
        }
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
        $outputFilename = App::$outputFolder;

        // output filename
        if (self::$name !== false) {
            $outputFilename .= App::GetConvertedFilename(self::$name);
            if ($suffix !== false) {
                $outputFilename .= '-' . $suffix;
            }
        } else if ($suffix !== false) {
            $outputFilename .= $suffix;
        } else {
            $outputFilename .= 'data';
        }

        $outputFilename .= '.' . App::GetOutputFileExtension();

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
    public static function GetCArray($name, $values, $numbase = 10, $large_array = false)
    {
        if ($large_array === true) {
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
        echo '** Spectrum Asset Maker v' . self::VERSION . ' - Chris Owen 2022 **' . CR . CR;
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
     * Text delimeter for processing strings
     */
    public static function GetStringDelimiter()
    {
        return self::$stringDelimiter;
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

    public static function objectToArray($object)
    {
        return @json_decode(@json_encode($object), 1);
    }
}
