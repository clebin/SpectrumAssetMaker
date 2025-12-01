<?php

namespace ClebinGames\SpectrumAssetMaker;

/**
 * Spectrum Asset Maker
 * Chris Owen 2025
 * 
 * Read Tiled map and tileset and save screen data for use on the Spectrum and Spectrum Next.
 * Load PNG/GIF graphics data and save as graphics data
 * 
 * Load multiple Tiled layers and save as individual screens
 * Add custom properites to attributes/tiles
 */
class App
{
    // app details
    public const VERSION = '2.0b1';
    public const RELEASE_YEAR = '2025';

    // output formats
    public const FORMAT_ASM = 'asm';
    public const FORMAT_C = 'c';
    public const FORMAT_BINARY = 'binary';

    // file extensions
    public const FILE_EXTENSION_PNG = 'png';
    public const FILE_EXTENSION_GIF = 'gif';

    // memory bank length (16k)
    public const BANK_LENGTH_BYTES = 16384;

    // memory page length (8k)
    public const PAGE_LENGTH_BYTES = 65536;

    // byte formats
    public const BINARY_FORMAT_ONE_BYTE = '1-byte';
    public const BINARY_FORMAT_TWO_BYTE = '2-byte';
    public const BINARY_FORMAT_1BIT = '1-bit';
    public const BINARY_FORMAT_4BIT = '4-bit';
    public const BINARY_FORMAT_8BIT = '8-bit';
    
    // naming
    public const NAMING_CAMELCASE = 'camelcase';
    public const NAMING_UNDERSCORES = 'underscores';

    // compression
    public const COMPRESSION_NONE = false;
    public const COMPRESSION_RLE = 'rle';
    public const COMPRESSION_ZX0 = 'zx0';

    // terminal colours
    public const TERMINAL_BOLD = "\033[1m";
    public const TERMINAL_BG_BLACK = "\033[40m";
    public const TERMINAL_BG_MAGENTA = "\033[105m";
    public const TERMINAL_BLUE = "\033[34m";
    public const TERMINAL_RED = "\033[31m";
    public const TERMINAL_MAGENTA = "\033[95m"; // 35m
    public const TERMINAL_GREEN = "\033[32m";
    public const TERMINAL_CYAN = "\033[96m"; // 34m
    public const TERMINAL_YELLOW = "\033[33m";
    public const TERMINAL_WHITE = "\033[0m";

    // colour constants
    public const COLOUR_BLACK = 'black';
    public const COLOUR_BLUE = 'blue';
    public const COLOUR_RED = 'red';
    public const COLOUR_MAGENTA = 'magenta';
    public const COLOUR_GREEN = 'green';
    public const COLOUR_CYAN = 'cyan';
    public const COLOUR_YELLOW = 'yellow';
    public const COLOUR_WHITE = 'white';

    // verbosity
    public const VERBOSITY_SILENT = 0;
    public const VERBOSITY_NORMAL = 1;
    public const VERBOSITY_VERBOSE = 2;

    // layer types
    public const LAYER_TYPE_ALL = 'all';
    public const LAYER_TYPE_TILELAYER = 'tilelayer';
    public const LAYER_TYPE_OBJECTGROUP = 'objectgroup';

    // next bitmap format
    public const NEXT_BITMAP_FORMAT_ROWS = 'rows';
    public const NEXT_BITMAP_FORMAT_COLUMNS = 'columns';
    
    // options
    public static array $options = [];
    
    // binary formats
    public static array $binaryFormatsSupported = [
        self::BINARY_FORMAT_ONE_BYTE,
        self::BINARY_FORMAT_TWO_BYTE,
        self::BINARY_FORMAT_1BIT,
        self::BINARY_FORMAT_4BIT,
        self::BINARY_FORMAT_8BIT
    ];

    // classic colours
    public static array $coloursSupported = [
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
    public static array $rgbColours = [
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
    public static array $formatsSupported = ['asm', 'c'];

    public static string $namingConvention = self::NAMING_CAMELCASE;
    public static array $namingConventionsSupported = [
        'camelcase',
        'underscores',
        'titlecase'
    ];

    public static array $compressionSupported = [
        self::COMPRESSION_RLE,
        self::COMPRESSION_ZX0
    ];

    // layer types

    public static array $layerTypesSupported = [
        self::LAYER_TYPE_ALL,
        self::LAYER_TYPE_OBJECTGROUP,
        self::LAYER_TYPE_TILELAYER
    ];

    public static bool $saveGameProperties = false;
    private static string $stringDelimiter = CR;
    public static int $verbosity = self::VERBOSITY_NORMAL;

    // list of output files - for binaries.lst
    private static array $outputFiles = [];

    // errors
    private static int $numErrors = 0;
    
    // configuration
    public static string $configFile;
    
    // sections to process
    public static array $sectionsToProcess = [];

    // names to process
    public static array $namesToProcess = [];

    public static bool $createReferenceFile = true;

    public static string $paperColour = self::COLOUR_WHITE;

    // next screen format - default to banks laid out as rows
    public static string $nextScreenFormat = self::NEXT_BITMAP_FORMAT_ROWS;

    // character set
    public static array $charset = [
        ' ',
        '!',
        '"',
        '#',
        '$',
        '%',
        '&',
        '\'',
        '(',
        ')',
        '*',
        '+',
        ',',
        '-',
        '.',
        '/',
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        ':',
        ';',
        '<',
        '=',
        '>',
        '?',
        '@',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        '[',
        ']',
        '\\',
        '^',
        '_',
        '£',
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z'
    ];

    /**
     * Start the tool
     */
    public static function Run($options) : void
    {
        self::$options = $options;

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

        // specify json config file
        if (isset($options['config'])) {
            self::$configFile = $options['config'];
        }
        
        // specify datatypes to use
        if( isset($options['section'])) {
            self::$sectionsToProcess = explode(',', $options['section']);
        }

        // specify names to use
        if( isset($options['name'])) {
            self::$namesToProcess = explode(',', $options['name']);
        }

        Configuration::Process();

        // display errors
        self::ShowErrors();

        echo CR . '' . self::GetTerminalStripes() .
            ' Asset Generation Complete' . CR . CR;
    }

    /**
     * Output errors that have occurred during asset generation
     */
    public static function ShowErrors() : void
    {
        if (self::$numErrors > 0) {
            echo CR . self::TERMINAL_RED . self::$numErrors . ' ' .
            ( self::$numErrors == 1 ? 'error' : 'errors') . ' occured';
            echo CR;
        }
    }

    /**
     * Get Speccy style stripes for the terminal output
     */
    public static function GetTerminalStripes() : string
    {
        return self::TERMINAL_RED . '/' .
            self::TERMINAL_YELLOW . '/' .
            self::TERMINAL_GREEN . '/' .
            self::TERMINAL_CYAN . '/' .
            self::TERMINAL_WHITE;
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
    public static function GetAsmArray($name, $values, $numbase = 10, $length = false, $public = true) : string
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
     * Compress data array using ZX0 (requires Java)
     */
    public static function CompressArrayZX0($filename) : void
    {
        $zx0_path = getenv('ZX0_PATH');

        if ($zx0_path === false) {
            self::AddError('To use ZX0 compression you must set \'ZX0_PATH\' local environment variable pointing to the ZX0 executable or jarfile.');
        }

        // remove old file if necessary
        if (file_exists($filename . '.zx0')) {
            unlink($filename . '.zx0');
        }

        // java version
        if (strpos($zx0_path, '.jar') > 0) {
            shell_exec('java -jar ' . $zx0_path . ' -c ' . $filename);
        }
        // exe version
        else {
            shell_exec($zx0_path . ' -c ' . $filename);
        }
    }

    /**
     * Compress data array using run-length encoding
     */
    public static function CompressArrayRLE($name, $input, $add_length = true) : array
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

        // output information
        if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
            self::OutputMessage(
                'Compression ' . $inputSize . ' -> ' . $outputSize . ' bytes. Saved ' . round((($inputSize - $outputSize) / $inputSize) * 100, 1) . '%)',
                'Tilemap',
                ($name !== false ? $name : 'array')
            );
        }

        return $output;
    }

    /**
     * Get C code for an array of pointers
     */
    public static function GetPointerArrayC($arrayName, $itemsBaseName, $size = 0) : string
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
    public static function GetConvertedCodeName($source_name, $format) : string
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

        $name = '_' .ltrim($name, '_');

        return $name;
    }

    /**
     * Convert a regular name to constant
     */
    public static function GetConvertedConstantName($source_name) : string
    {
        return strtoupper(self::GetConvertedCodeNameUnderscores($source_name));
    }

    /** 
     * Convert a regular name to use underscores 
     */
    public static function GetConvertedCodeNameUnderscores($source_name) : string
    {
        return strtolower(str_replace(['-', ' '], '_', $source_name));
    }

    /**
     * Convert a regular name to camel-case
     */
    public static function GetConvertedCodeNameCamelCase($source_name) : string
    {
        return lcfirst(implode('', array_map('ucfirst', explode(' ', str_replace('-', ' ', $source_name)))));
    }

    /**
     * Convert a regular name to title-case
     */
    public static function GetConvertedCodeNameTitleCase($source_name) : string
    {
        return implode('', array_map('ucfirst', explode(' ', str_replace('-', ' ', $source_name))));
    }

    /**
     * Convert regular name to a filename format
     */
    public static function GetConvertedFilename($source_name) : string
    {
        return strtolower(str_replace(' ', '-', $source_name));
    }

    /**
     * Add to errors list
     */
    public static function AddError($message, $module = false, $name = false) : void
    {
        self::$numErrors++;

        echo self::TERMINAL_RED . 'Error! ';

        self::OutputMessage($message, $module, $name);
    }

    public static function AddWarning($message, $module = false, $name = false) : void
    {
        echo self::TERMINAL_BG_MAGENTA . 'Warning! ';

        self::OutputMessage($message, $module, $name);        
    }

    public static function OutputMessage($message, $module = false, $name = false) : void
    {
        if( $module !== false) {
            echo ' '.self::TERMINAL_CYAN . $module;
        }

        if( $name !== false ) {
            echo self::TERMINAL_WHITE . ' [' .
            self::TERMINAL_MAGENTA . $name .
            self::TERMINAL_WHITE . ']';
        }

        echo ' '.self::TERMINAL_YELLOW . rtrim($message, '.') .
        self::TERMINAL_WHITE . '.' . CR;
    }

    /**
     * Text delimeter for processing strings
     */
    public static function GetStringDelimiter() : string
    {
        return self::$stringDelimiter;
    }

    /**
     * Did an error occur?
     */
    public static function DidErrorOccur() : bool
    {
        return (self::$numErrors > 0 ? true : false);
    }

    public static function AddOutputFile($path) : void
    {
        if (!in_array($path, self::$outputFiles))
            self::$outputFiles[] = $path;
    }

    public static function ObjectToArray($object) : array
    {
        return @json_decode(@json_encode($object), 1);
    }

    /**
     * Get binaries.lst file with list of screen files
     */
    public static function ProcessAssetsLst($assetsLstFolder = '') : void
    {
        $strAssets = '';

        $assetsLstFolder = rtrim($assetsLstFolder, '/') . '/';

        sort(App::$outputFiles);

        foreach (App::$outputFiles as $path) {
            $strAssets .= str_replace($assetsLstFolder, '', $path) . CR;
        }

        file_put_contents($assetsLstFolder . 'assets.lst', $strAssets);
    }

    /**
     * Return whether tool is in verbose mode
     */
    public static function GetVerbosity() : int
    {
        return self::$verbosity;
    }
}
