<?php

namespace ClebinGames\SpectrumAssetMaker;

define('CR', "\n");

require("src/App.php");
require("src/Configuration.php");
require("src/CliTools.php");
require("src/Attribute.php");
require("src/Tile.php");
require("src/ObjectTypes.php");
require("src/GameObject.php");
require("src/Datatypes/Datatype.php");
require("src/Datatypes/BlankData.php");
require("src/Datatypes/ArrayData.php");
require("src/Datatypes/Tilemap.php");
require("src/Datatypes/TilemapNext.php");
require("src/Datatypes/TileLayer.php");
require("src/Datatypes/TileLayerNext.php");
require("src/Datatypes/Tileset.php");
require("src/Datatypes/MapPaths.php");
require("src/Datatypes/Graphics.php");
require("src/Datatypes/GraphicsClassic.php");
require("src/Datatypes/GraphicsNext.php");
require("src/Datatypes/TileGraphicsNext.php");
require("src/Datatypes/PaletteNext.php");
require("src/Datatypes/Sprite.php");
require("src/Datatypes/SpriteNext.php");
require("src/Datatypes/ObjectLayer.php");
require("src/Datatypes/Text.php");
require("src/Datatypes/Screen.php");
require("src/Datatypes/ScreenNext.php");
require("src/Datatypes/BitmapNext.php");

// read filenames from command line arguments
$options = getopt('', [
    'help::',
    'verbosity::',
    'config::',
    'section::'
]);

// run
App::Run($options);

echo CR;
