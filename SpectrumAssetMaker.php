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
require("src/Datatypes/TileLayer.php");
require("src/Datatypes/TileLayerNextTwoBytes.php");
require("src/Datatypes/Tileset.php");
require("src/Datatypes/MapPaths.php");
require("src/Datatypes/Graphics.php");
require("src/Datatypes/GraphicsClassic.php");
require("src/Datatypes/GraphicsNext.php");
require("src/Datatypes/TileGraphicsNext.php");
require("src/Datatypes/PaletteNext.php");
require("src/Datatypes/PaletteNextOneByte.php");
require("src/Datatypes/PaletteNextTwoBytes.php");
require("src/Datatypes/Sprite.php");
require("src/Datatypes/SpriteNext4Bit.php");
require("src/Datatypes/SpriteNext8Bit.php");
require("src/Datatypes/ObjectMap.php");
require("src/Datatypes/Text.php");
require("src/Datatypes/Screen.php");
require("src/Datatypes/ScreenNextLayer2.php");
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
