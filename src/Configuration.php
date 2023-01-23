<?php

namespace ClebinGames\SpectrumAssetMaker;

use \ClebinGames\SpectrumAssetMaker\Datatypes\BlankData;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tilemap;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Tileset;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Graphics;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Sprite;
use \ClebinGames\SpectrumAssetMaker\Datatypes\Text;

class Configuration
{
    private static $configPath = '';
    private static $config = [];

    // default settings
    public static $createBinariesLst = false;
    public static $outputFolder = "./assets";

    // data types
    private static $settings = [];
    private static $sprites = [];
    private static $tilemaps = [];
    private static $tilesets = [];
    private static $graphics = [];
    private static $blankData = [];
    private static $text = [];

    public static function Setup($configPath)
    {
        if (!file_exists($configPath)) {

            echo 'Error: Config file not found';
            return;
        }

        // read config file
        $json = file_get_contents($configPath);

        $config = json_decode($json, true);

        // settings
        if (isset($config['settings'])) {
            self::ReadSettings($config['settings']);
        }

        // sprites
        if (isset($config['sprites'])) {
            self::ReadSprites($config['sprites']);
        }

        // tilemaps
        if (isset($config['tilemaps'])) {
            self::ReadTilemaps($config['tilemaps']);
        }

        // tilesets
        if (isset($config['tilesets'])) {
            self::ReadTilesets($config['tilesets']);
        }

        // graphics
        if (isset($config['graphics'])) {
            self::ReadGraphics($config['graphics']);
        }

        // text
        if (isset($config['text'])) {
            self::ReadText($config['text']);
        }

        // blank data
        if (isset($config['blank-data'])) {
            self::ReadBlankData($config['blank-data']);
        }

        // save in case we need it
        self::$config = $config;
    }

    private static function ReadSprites($config)
    {
        foreach ($config as $item) {
            $sprite = new Sprite($item);
            $sprite->Process();
        }
    }

    private static function ReadTilemaps($config)
    {
        foreach ($config as $item) {
            $tilemapObj = new Tilemap($item);
            $tilemapObj->Process();
        }
    }

    private static function ReadTilesets($config)
    {
        foreach ($config as $item) {
        }
    }

    private static function ReadGraphics($config)
    {
        foreach ($config as $item) {
            $graphics = new Graphics($item);
            $graphics->Process();
        }
    }

    private static function ReadBlankData($config)
    {
        foreach ($config as $item) {
            $blankDataObj = new BlankData($item);
            $blankDataObj->Process();
        }
    }

    private static function ReadText($config)
    {
        foreach ($config as $item) {
            $textObj = new Text($item);
            $textObj->Process();
        }
    }

    private static function ReadSettings($config)
    {
        // create binaries lst
        if (isset($config['create-binaries-lst'])) {
            self::$createBinariesLst = $config['create-binaries-lst'];
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
