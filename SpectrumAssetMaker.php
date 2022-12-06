<?php

namespace ClebinGames\SpectrumAssetMaker;

define('CR', "\n");

require("src/App.php");
require("src/CliTools.php");
require("src/Attribute.php");
require("src/Tile.php");
require("src/ObjectTypes.php");
require("src/GameObject.php");
require("src/Datatypes/Datatype.php");
require("src/Datatypes/BlankData.php");
require("src/Datatypes/TileLayer.php");
require("src/Datatypes/Tileset.php");
require("src/Datatypes/TilesetXML.php");
require("src/Datatypes/Tilemap.php");
require("src/Datatypes/TilemapXML.php");
require("src/Datatypes/Graphics.php");
require("src/Datatypes/Sprite.php");
require("src/Datatypes/ObjectMap.php");
require("src/Datatypes/ObjectMapXML.php");
require("src/Datatypes/Text.php");

// read filenames from command line arguments
$options = getopt('', [
    'help::',
    'name::',
    'map::',
    'blank-data::',
    'text::',
    'string-delimiter::',
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
App::Run($options);

echo CR;
