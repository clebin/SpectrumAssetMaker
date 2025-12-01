<?php

namespace ClebinGames\SpectrumAssetMaker;

use \ClebinGames\SpectrumAssetMaker\Datatypes\ArrayData;
use \ClebinGames\SpectrumAssetMaker\Datatypes\BitmapNext;
use \ClebinGames\SpectrumAssetMaker\Datatypes\BlankData;
use \ClebinGames\SpectrumAssetMaker\Datatypes\FontNext;
use \ClebinGames\SpectrumAssetMaker\Datatypes\GraphicsClassic;
use \ClebinGames\SpectrumAssetMaker\Datatypes\PaletteNext;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Screen;
use \ClebinGames\SpectrumAssetMaker\Datatypes\ScreenNext;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Sprite;
use \ClebinGames\SpectrumAssetMaker\Datatypes\SpriteNext;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Text;
use \ClebinGames\SpectrumAssetMaker\Datatypes\TileGraphicsNext;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tilemap;
use \ClebinGames\SpectrumAssetMaker\Datatypes\TilemapNext;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tileset;

class Configuration
{
    private static string $configPath = '';
    private static array $config = [];
    private static array $settings = [];

    // map config sections to datatype
    private static array $sectionDatatypeMapping = [
        "sprite" => Sprite::class,
        "sprite-next" => SpriteNext::class,
        "tilemap" => Tilemap::class,
        "tilemap-next" => TilemapNext::class,
        "tileset" => Tileset::class,
        "graphics" => GraphicsClassic::class,
        "tile-graphics-next" => TileGraphicsNext::class,
        "text" => Text::class,
        "screen" => Screen::class,
        "screen-next" => ScreenNext::class,
        "bitmap-next" => BitmapNext::class,
        "palette-next" => PaletteNext::class,
        "blank-data" => BlankData::class,
        "array-data" => ArrayData::class,
        "font-next" => FontNext::class,
        
        // deprecated labels
        "sprites" => Sprite::class,
        "tilemaps" => Tilemap::class,
        "tilesets" => Tileset::class,
        "screens" => Screen::class
    ];
    
    public static function Process() : void
    {
        if (!file_exists(App::$configFile)) {
            echo 'Error: Config file not found';
            return;
        }

        echo 'Reading config file: ' . App::$configFile . CR;

        // read config file
        $json = file_get_contents(App::$configFile);

        try {
            $config = json_decode($json, true);
        } catch (\Exception $e) {
            echo 'Error reading JSON:' . $e;
            exit();
        }

        // check if json was parsed
        if ( !is_array($config) ||sizeof((array) $config) == 0) {
            App::AddError('JSON configuration couldn\'t be parsed correctly.');
            return;
        }

        // settings
        if (isset($config['settings'])) {
            self::ReadSettings($config['settings']);
        }

        // sections
        foreach(self::$sectionDatatypeMapping as $name => $class) {

            // if section is set and we're doing all datatypes or datatype is specified
            if( isset($config[$name]) && 
                (sizeof(App::$sectionsToProcess) == 0 || in_array($name, App::$sectionsToProcess))) {
                self::ReadSection($class, $config[$name]);
            }
        }

        // save in case we need it
        self::$config = $config;
    }

    private static function ReadSection($datatypeName, $config) : void
    {
        foreach($config as $item)
        {
            // process item
            if( sizeof(App::$namesToProcess) == 0 || in_array($item['name'], App::$namesToProcess)) {
                $datatype = new $datatypeName($item);
                $datatype->Process();
            }
        }
    }

    private static function ReadSettings($config) : void
    {
        // create binaries lst
        if (isset($config['create-assets-list'])) {

            if( is_string($config['create-assets-list']) ) {
                App::$assetsLstFilename = $config['create-assets-list'];
            }
            App::$createAssetsLst = $config['create-assets-list'];
        }

        // base output folder
        if (isset($config['output-folder'])) {
            App::$outputFolder = $config['output-folder'];
        }

        // object types
        if (isset($config['object-types'])) {
            ObjectTypes::ProcessFile($config['object-types']);
        }

        // naming convention
        if (isset($config['naming'])) {
            App::$namingConvention = $config['naming'];
        }

        // next screen resolution
        if( isset($config['next-screen-format']) && 
            $config['next-screen-format'] == App::NEXT_BITMAP_FORMAT_COLUMNS) {

            App::$nextScreenFormat = App::NEXT_BITMAP_FORMAT_COLUMNS;
        }

        // create binary reference file default
        if( isset($config['create-binary-reference-file']) && 
            $config['create-binary-reference-file'] === false ) {
                App::$createReferenceFile = false;
        }
        
        // default paper colour
        if (isset($config['paper-colour']) && 
            in_array($config['paper-colour'], App::$coloursSupported)) {
            App::$paperColour = $config['paper-colour'];
        }

        // save all settings here
        self::$settings = $config;
    }

    public static function GetOutputFolder() : string
    {
        return self::$outputFolder;
    }
}
