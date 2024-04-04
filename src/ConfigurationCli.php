<?php

namespace ClebinGames\SpectrumAssetMaker;

use \ClebinGames\SpectrumAssetMaker\Datatypes\BlankData;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tilemap;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tileset;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Graphics;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Sprite;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Text;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Screen;
use \ClebinGames\SpectrumAssetMaker\Datatypes\ArkosTracker;

class ConfigurationCli
{
    public static $format = App::FORMAT_ASM;

    // set graphics paper colour
    public static $paperColour = App::COLOUR_WHITE;

    // naming
    public static $name = false;
    public static $useLayerNames = false;
    public static $replaceFlashWithSolid = false;

    // compression
    public static $compression = false;

    // input filenames
    private static $spriteFilename = false;
    private static $maskFilename = false;
    private static $mapFilename = false;
    private static $tilesetFilename = false;
    private static $graphicsFilename = false;
    private static $screenFilename = false;

    // tileset properties
    public static $addTilesetProperties = false;

    // text
    private static $textFilename = false;
    private static $stringDelimiter;

    // tilemap layers
    private static $ignoreHiddenLayers = false;
    private static $layerType = 'all';
    public static $generatePaths = false;

    // blank data
    private static $blankDataSize = 0;

    // object types
    private static $objectTypesFilename = false;

    // arkos
    private static $arkosFilename = false;
    private static $arkosCommand;

    // more settngs
    private static $outputFolder = '.';
    private static $addDimensions = false;
    private static $outputFilename = false;
    private static $spriteWidth = false;

    // assembly section
    public static $section = 'rodata_user';

    // save game properties
    public static $saveSolidData = false;
    public static $saveLethalData = false;

    /**
     * Set up the tool using parameters passed on the command line
     */
    public static function Setup($options)
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

        // add dimensions
        if (isset($options['add-dimensions'])) {
            self::$addDimensions = true;
        }

        // layer type
        if (
            isset($options['layer-type']) &&
            in_array($options['layer-type'], App::$layerTypesSupported)
        ) {
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
            self::$addTilesetProperties = true;
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

        // screen
        if (isset($options['screen'])) {
            self::$screenFilename = $options['screen'];
        }

        // paper colour
        if (isset($options['paper-colour']) && in_array($options['paper-colour'], App::$coloursSupported)) {
            self::$paperColour = $options['paper-colour'];
        }

        // blank data
        if (isset($options['blank-data'])) {
            self::$blankDataSize = $options['blank-data'];
        }

        // object types
        if (isset($options['object-types'])) {
            ObjectTypes::ProcessFile($options['object-types']);
        }

        // arkos
        if (isset($options['arkos'])) {
            self::$arkosFilename = $options['arkos'];

            if (isset($options['arkos_command'])) {
                self::$arkosCommand = $options['arkos_command'];
            }
        }

        // format
        if (isset($options['format'])) {
            self::$format = $options['format'];
        }

        // is format supported?
        if (!in_array(self::$format, App::$formatsSupported)) {
            echo 'Error: Format not supported.' . CR;
            return false;
        }

        // output folder
        if (isset($options['output-folder'])) {
            self::$outputFolder = rtrim($options['output-folder'], '/') . '/';
        }

        // naming
        if (isset($options['naming'])) {
            App::$namingConvention = $options['naming'];
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

        if (self::$compression !== false && !in_array(self::$compression, App::$compressionSupported)) {
            echo 'Error: Compression type not supported.' . CR;
            return false;
        }

        self::Process();
    }

    /**
     * Run the tool
     */
    public static function Process()
    {
        $baseConfig = [
            'name' => self::$name,
            'output-folder' => self::$outputFolder,
            'format' => self::$format,
            'section' => self::$section,
        ];

        // process tileset graphics
        if (self::$graphicsFilename !== false) {
            $graphics = new Graphics([
                'name' => self::$name . (self::$tilesetFilename !== false ? '-graphics' : ''),
                'output-folder' => self::$outputFolder,
                'format' => self::$format,
                'section' => self::$section,
                'input' => self::$graphicsFilename,
                'paper-colour' => self::$paperColour
            ]);

            $graphics->Process();
        }

        // text
        if (self::$textFilename !== false) {

            $datatype = new Text(array_merge(
                $baseConfig,
                [
                    'input' => self::$textFilename
                ]
            ));
            $datatype->Process();
        }

        // blank data
        if (self::$blankDataSize > 0) {
            $datatype = new BlankData(array_merge(
                $baseConfig,
                [
                    'size' => self::$blankDataSize
                ]
            ));
            $datatype->Process();
        }

        // process tilemaps
        if (self::$mapFilename !== false) {

            $tilemap = new Tilemap(array_merge($baseConfig, [
                'input' => self::$mapFilename,
                'add-dimensions' => self::$addDimensions,
                'ignore-hidden-layers' => self::$ignoreHiddenLayers,
                'object-types' => self::$objectTypesFilename,
                'tileset' => self::$tilesetFilename,
                'compression' => self::$compression,
                'generate-paths' => self::$generatePaths,
                'layer-types' => self::$layerType
            ]));

            $tilemap->Process();
        }
        // tileset not associated with tilemap
        else if (self::$tilesetFilename !== false) {
            $tileset = new Tileset(array_merge(
                $baseConfig,
                [
                    'input' => self::$tilesetFilename,
                    'add-tileset-properties' => self::$addTilesetProperties,
                    'replace-flash-with-solid' => self::$replaceFlashWithSolid
                ]
            ));

            $tileset->Process();
        }

        // process sprite
        if (self::$spriteFilename !== false) {
            $sprite = new Sprite(array_merge(
                $baseConfig,
                [
                    'input' => self::$spriteFilename,
                    'mask' => self::$maskFilename
                ]
            ));
            $sprite->Process();
        }

        // process screen
        if (self::$screenFilename !== false) {
            $screen = new Screen(array_merge(
                $baseConfig,
                [
                    'input' => self::$screenFilename
                ]
            ));
            $screen->Process();
        }

        // process arkos
        if (self::$arkosFilename !== false) {
            $arkos = new ArkosTracker(array_merge(
                $baseConfig,
                [
                    'input' => self::$arkosFilename,
                    'command' => self::$arkosCommand
                ]
            ));
            $arkos->Process();
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
     * Adding dimensions?
     */
    public static function GetAddDimensions()
    {
        return self::$addDimensions;
    }
}
