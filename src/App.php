<?php

namespace ClebinGames\SpectrumAssetMaker;

use \ClebinGames\SpectrumAssetMaker\Datatypes\BlankData;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tilemap;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tileset;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Graphics;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Sprite;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Text;

/**
 * Spectrum Asset Maker
 * Chris Owen 2024
 * 
 * Read Tiled map and tileset and save screen data for use on the Spectrum.
 * Load PNG/GIF graphics data and save as graphics data
 * 
 * Load multiple Tiled layers and save as individual screens
 * Add custom properites to attributes/tiles
 */
class App
{
    const VERSION = '1.0b3';
    const RELEASE_YEAR = '2024';

    // constants
    const FORMAT_ASM = 'asm';
    const FORMAT_C = 'c';
    const NAMING_CAMELCASE = 'camelcase';
    const NAMING_UNDERSCORES = 'underscores';

    // terminal colours
    const TERMINAL_BOLD = "\033[1m";
    const TERMINAL_BG_BLACK = "\033[40m";
    const TERMINAL_BG_MAGENTA = "\033[105m";
    const TERMINAL_BLUE = "\033[34m";
    const TERMINAL_RED = "\033[31m";
    const TERMINAL_MAGENTA = "\033[95m"; // 35m
    const TERMINAL_GREEN = "\033[32m";
    const TERMINAL_CYAN = "\033[96m"; // 34m
    const TERMINAL_YELLOW = "\033[33m";
    const TERMINAL_WHITE = "\033[0m";

    // colour constants
    const COLOUR_BLACK = 'black';
    const COLOUR_BLUE = 'blue';
    const COLOUR_RED = 'red';
    const COLOUR_MAGENTA = 'magenta';
    const COLOUR_GREEN = 'green';
    const COLOUR_CYAN = 'cyan';
    const COLOUR_YELLOW = 'yellow';
    const COLOUR_WHITE = 'white';

    const VERBOSITY_SILENT = 0;
    const VERBOSITY_NORMAL = 1;
    const VERBOSITY_VERBOSE = 2;

    public static $coloursSupported = [
        self::COLOUR_BLACK,
        self::COLOUR_BLUE,
        self::COLOUR_RED,
        self::COLOUR_MAGENTA,
        self::COLOUR_GREEN,
        self::COLOUR_CYAN,
        self::COLOUR_YELLOW,
        self::COLOUR_WHITE
    ];

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

    // current output format
    public static $formatsSupported = ['asm', 'c'];

    public static $namingConvention = self::NAMING_CAMELCASE;
    public static $namingConventionsSupported = [
        'camelcase',
        'underscores',
        'titlecase'
    ];

    public static $compressionSupported = ['rle'];
    public static $layerTypesSupported = [
        'all',
        'objectgroup',
        'tilelayer'
    ];
    public static $saveGameProperties = false;
    private static $stringDelimiter = CR;
    public static $verbosity = self::VERBOSITY_NORMAL;

    // list of output files - for binaries.lst
    private static $outputFiles = [];

    // errors
    private static $error = false;
    private static $errorDetails = [];

    /**
     * Start the tool
     */
    public static function Run($options)
    {
        echo CR . '' .
            self::GetTerminalStripes() .
            ' Spectrum Asset Maker ' .
            self::TERMINAL_GREEN . '[v' . self::VERSION . ', ' .
            self::TERMINAL_GREEN . 'Chris Owen ' . self::RELEASE_YEAR . ']' .
            self::TERMINAL_WHITE . '' . CR . CR;

        // verbosity
        if (isset($options['verbosity'])) {
            self::$verbosity = $options['verbosity'];
        }

        // use json config file
        if (isset($options['config'])) {
            Configuration::Setup($options['config']);
        }
        // use command line args
        else {
            ConfigurationCli::Setup($options);
        }

        // display errors
        if (self::$error === true) {
            echo 'Errors (' . sizeof(self::$errorDetails) . '): ' . implode('. ', self::$errorDetails);
            return false;
        }

        echo CR . '' . self::GetTerminalStripes() .
            ' Asset Generation Complete' . CR . CR;
    }

    /**
     * Get Speccy style stripes for the terminal output
     */
    public static function GetTerminalStripes()
    {
        return self::TERMINAL_RED . '/' .
            self::TERMINAL_YELLOW . '/' .
            self::TERMINAL_GREEN . '/' .
            self::TERMINAL_CYAN . '/' .
            self::TERMINAL_WHITE;
    }

    /**
     * Check if rgb colour matches paper colour
     */
    public static function colourIsPaper($rgb, $paperColour, $filetype = 'gif')
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

            $paper = self::$rgbColours[$paperColour];

            if ($r != $paper[0] || $g != $paper[1] || $b != $paper[2]) {
                return false;
            } else {
                return true;
            }
        }
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
            $str .= CR . 'PUBLIC ' . $name . CR;
        }

        // output paper/ink/bright/flash
        $str .= CR . '.' . $name;

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

        if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
            App::OutputMessage(
                'Tilemap',
                ($name !== false ? $name : 'array'),
                'Compression ' . $inputSize . ' -> ' . $outputSize . ' bytes. Saved ' . round((($inputSize - $outputSize) / $inputSize) * 100, 1) . '%)'
            );
        }

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
    public static function GetConvertedCodeName($source_name, $format)
    {
        $name = '';
        switch (self::$namingConvention) {
            case 'underscores':
                $name = self::GetConvertedCodeNameUnderscores($source_name);
                break;
            case 'titlecase';
                $name = self::GetConvertedCodeNameTitleCase($source_name);
            default:
                $name = self::GetConvertedCodeNameCamelCase($source_name);
                break;
        }

        if ($format == App::FORMAT_ASM) {
            $name = '_' . $name;
        }

        return $name;
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
     * Did an error occur?
     */
    public static function DidErrorOccur()
    {
        return self::$error;
    }

    public static function AddOutputFile($path)
    {
        if (!in_array($path, self::$outputFiles))
            self::$outputFiles[] = $path;
    }

    public static function ObjectToArray($object)
    {
        return @json_decode(@json_encode($object), 1);
    }

    /**
     * Get binaries.lst file with list of screen files
     */
    public static function ProcessAssetsLst($binariesLstFolder = '')
    {
        $strBinaries = '';
        $binariesLstFolder = rtrim($binariesLstFolder, '/') . '/';

        sort(App::$outputFiles);

        foreach (App::$outputFiles as $path) {
            $strBinaries .= str_replace($binariesLstFolder, '', $path) . CR;
        }

        file_put_contents($binariesLstFolder . 'assets.lst', $strBinaries);
    }

    /**
     * Return output message in a standard format
     */
    public static function OutputMessage($module, $name, $message)
    {
        echo self::TERMINAL_CYAN . $module .
            self::TERMINAL_WHITE . ' [' .
            self::TERMINAL_MAGENTA . $name .
            self::TERMINAL_WHITE . '] ' .
            self::TERMINAL_YELLOW . rtrim($message, '.') .
            self::TERMINAL_WHITE . '.' . CR;
    }

    /**
     * Return whether tool is in verbose mode
     */
    public static function GetVerbosity()
    {
        return self::$verbosity;
    }
}
