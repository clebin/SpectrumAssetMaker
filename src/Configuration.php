<?php

namespace ClebinGames\SpectrumAssetMaker;

use \ClebinGames\SpectrumAssetMaker\Datatypes\BlankData;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tilemap;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tileset;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Graphics;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Sprite;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Text;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Screen;
use \ClebinGames\SpectrumAssetMaker\Datatypes\ArrayData;

class Configuration
{
    private static $configPath = '';
    private static $config = [];
    private static $settings = [];

    // default settings
    public static $createBinariesLst = false;
    public static $outputFolder = "./assets";

    // map config sections to datatype
    private static $sectionDatatypeMapping = [
        "sprites" => Sprite::class,
        "tilemaps" => Tilemap::class,
        "tilesets" => Tileset::class,
        "graphics" => Graphics::class,
        "text" => Text::class,
        "Screen" => Screen::class,
        "blank-data" => BlankData::class,
        "array-data" => ArrayData::class
    ];

    public static function Process($configPath, $sectionsInUse = [])
    {
        if (!file_exists($configPath)) {

            echo 'Error: Config file not found';
            return;
        }

        echo 'Reading config file: ' . $configPath . CR;

        // read config file
        $json = file_get_contents($configPath);

        try {
            $config = json_decode($json, true);
        } catch (\Exception $e) {
            echo 'Error reading JSON:' . $e;
            exit();
        }

        // check if json was parsed
        if (sizeof((array) $config) == 0) {
            App::AddError('JSON configuration couldn\'t be parsed correctly.');
        }

        // settings
        if (isset($config['settings'])) {
            self::ReadSettings($config['settings']);
        }

        // sections
        foreach(self::$sectionDatatypeMapping as $name => $class) {

            // if section is set and we're doing all datatypes or datatype is specified
            if( isset($config[$name]) && (sizeof($sectionsInUse) == 0 || in_array($name, $sectionsInUse))) {
                self::ReadSection($class, $config[$name]);
            }
        }

        // save in case we need it
        self::$config = $config;

        // save binaries.lst
        if (self::$createBinariesLst === true) {
            App::ProcessAssetsLst(self::$outputFolder);
        }
    }

    private static function ReadSection($datatypeName, $config)
    {
        foreach($config as $item)
        {
            $datatype = new $datatypeName($item);
            $datatype->Process();
        }
    }

    private static function ReadSettings($config)
    {
        // create binaries lst
        if (isset($config['create-assets-list'])) {
            self::$createBinariesLst = $config['create-assets-list'];
        }

        // base output folder
        if (isset($config['output-folder'])) {
            self::$outputFolder = $config['output-folder'];
        }

        // object types
        if (isset($config['object-types'])) {
            ObjectTypes::ProcessFile($config['object-types']);
        }

        // naming convention
        if (isset($config['naming'])) {
            App::$namingConvention = $config['naming'];
        }

        // save all settings here
        self::$settings = $config;
    }

    public static function GetOutputFolder()
    {
        return self::$outputFolder;
    }
}
