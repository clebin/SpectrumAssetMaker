# Spectrum Asset Maker
## C. Owen 2024

Command-line utility for the creation of a wide range of ZX Spectrum assets for use with z88dk and sp1. Intended to be a one-stop shop for all your game assets.

## Output formats

* Graphics data

* Masked/Unmasked Sprite (sp1 format)

* .SCR file (eg. loading screen)

* Tilemap - tile numbers, associated with a tileset

* Path Map - an array showing exits up/down/left/right for each square. Can be used to speed up pathfinding for complex AI.

* Tileset - Includes attribute colours, solid properties. Associated with a set of graphics.

* Object Map - object type, x, y, width, height

* Text data

* Blank (zeroed) data


## Input formats:

|Type|File expected|
|---|---|
|**Tilemap**|Tiled tilemap exported JSON (.tmj)||
|**Tileset**|Tiled tileset exported as JSON (.tsj)|
|**Object types**|Tiled Object Types XML file (.xml)|
|**Tileset graphics**|Black and white PNG or GIF (PNG recommended)|
|**Sprite**|Black and white PNG or GIF (PNG recommended)|
|**Sprite Mask**|Black and white PNG or GIF (PNG recommended)|
|**Text**|Plain text file|
|**SCR**|PNG file, 256 pixels by 192 pixels|

## Installation:

Install PHP with your favourite package manager:

> brew install php
> apt install php
etc.

## Usage without parameters

On the command line, run:

> php SpectrumAssetMaker.php

Running the script without parameters will prompt for each setting.

## Usage with command-line parameters

### Basic parameters:

Parameters can be pass directly to the tool to process a single asset of a small set of related assets.

**--name**=[name for output - this option overrides layer names for tile/object maps]

**--output-folder**=[folder path to place generated files]

**--format**=['c' or 'asm', default: asm]

**--section**=[assembly section to place code into, default: rodata_user]

### Parameters for graphics data

**--screen**=[path to png] (create a .scr file)

**--graphics**=[tiled graphics filename] (create graphics laid out tile-by-tile)


### Parameters for Tilemap/Object Map/Tileset Processing:

**--ignore-hidden-layers** (don't process hidden layers. Hidden layers are processed by default)

**--layer-type**=['all', 'objectgroup' or 'tilelayer'] (set which type of Tiled layers to process)

**--replace-flash-with-solid** (use the bit normally used for flash to denote a solid block)

**--object-types**=[object types XML file] (this is required for processing object maps)

**--object-props**=[path to object custom properties text file (see below for info)]

**--map**=[tilemap filename]

**--tileset**=[tileset filename]

**--paper-colour**=[black|blue|red|magenta|green|cyan|yellow|white] (colour to use as paper, everything else is taken as ink)

**--paper-colour**=[black|blue|red|magenta|green|cyan|yellow|white] (colour to use as paper, everything else is taken as ink)

**--add-dimensions** (add rows & columns, as the first two elements in the tilemap data arrays)

**--compression**=rle [enable RLE compression on tilemaps]


### Parameters for blank data

**--blank-data**=[size in bytes] (create blank data of a specified size)

### Parameters for text processing

**--text**=[text filename] (convert text into assembly or C array)

**--string-delimiter**=[character] (character to use for splitting strings into C arrays)


### Parameters for Sprite Processing:

**--sprite**=[sprite filename]

**--mask**=[sprite mask filename]

**--sprite-width**=[sprite width in 8 pixel columns]

## Usage with a JSON configuration file:

**--config=**=[path to JSON config file]

The tool can be configured to generate all supported assets associated with a project - sprites, tilemaps, graphics etc - in one pass, simplifying the build process.

#### create-assets-list

If the 'create-assets-list' settings, the tool will create an 'assets.lst' file in the specified output folder containing file-paths for all the generated assets. You can add this
file to your project settings with '@output-folder/assets.lst' to include the assets as part of your build.

Below is an example JSON configuration file. More JSON files are included in the 'sample' folder.

```json
{
    "settings": {
        "create-assets-list": true,
        "naming": "camelcase",
        "output-folder": "./assets",
        "object-types": "raw-assets/objects/objecttypes.xml"
    },
    "blank-data": [{
        "name": "level-tilemap",
        "size": 2208,
        "output-folder": "./assets/blank-data"
    }],
    "tilemaps": [{
        "map": "raw-assets/tilemaps/screens.tmj",
        "output-folder": "./assets/levels",
        "use-layer-names": true,
        "generate-paths": true,
        "path-width": 2,
        "path-height": 2,
        "path-map-style": "platform",
        "format": "asm",
        "compression": "rle",
        "ignore-hidden-layers": false,
        "section": "BANK_6",
        "tileset": {
            "name": "game-tiles",
            "tileset": "raw-assets/tilesets/game-tileset.tsj",
            "output-folder": "./assets/tilesets",
            "replace-flash-with-solid": true,
            "section": "BANK_4"
        }
    }],
    "tilesets": [{
        "name": "menu-tiles",
        "tileset": "raw-assets/main-menu/menu-tiles.tsj",
        "output-folder": "./assets/main-menu",
        "section": "BANK_4"
    }],
    "sprites": [
    {
        "name": "player-sprite",
        "input": "raw-assets/sprites/player-sprite.png",
        "mask": "raw-assets/sprites/player-sprite-mask.png",
        "paper-colour": "black",
        "output-folder": "./assets/sprites",
        "section": "BANK_0"
    }],
    "graphics": [{
            "name": "font",
            "input": "raw-assets/fonts/lander-bold.png",
            "paper-colour": "white",
            "output-folder": "./assets",
            "section": "BANK_0"
        }],
    "screens": [
        {
            "name": "loading-screen",
            "input": "raw-assets/loading-screen.png",
            "output-folder": "./assets"
        }
    ],
    "text": [{
		"name": "intro-text",
		"input": "raw-assets/intro.txt",
		"output-folder": "./assets/text",
	    "section": "BANK_0"
    }]
}
```

### Tileset format ###

Each tile in your tileset should have the following custom properties set:

* flash (boolean)

* bright (boolean)

* paper (number 0-7)

* ink (number 0-7)

* solid (boolean)

### Importing Tilemap layers ###

If --layer-type is set to 'all' (default) or 'tilelayer', the tool will create code for each tilemap layer. The tool will include hidden layers unless --ignore-hidden-layers is set to true.

The layer name will be used for variable and file naming, unless --name is specified.

### Generating path maps

'Path maps' can be pre-generated to speed up movement calculation in some situations, eg. for computer AI. 
This will create a byte for each square on the map that specifies which directions a player/character may move from that square.

This feature requires 'solid' (true/false) and optionally 'ladder' (true/false) to be set on tileset tiles in Tiled.

The path-width and path-height settings are used to specify how many squares wide/high the path needs to be.

The 'path-map-style' property can be set to 'overhead' or 'platform'. In overhead mode, a character can move in all 4 directions if there's a space. In 'platform' mode, the character can only move up and down if the tiles have 'ladder' set to true.

The byte format of a square in the path map is as follows:

```
[0][0][0][0][up][down][left][right]
```

For example, a square with exits in all 4 directions would be represented as:

```
00001111
```

### Importing Object layers ###

If --layer-type is set to 'all' (default) or 'objectgroup', the tool will create code for each objectgroup layer.

To add the width & height of objects, add a boolean 'add-dimensions' property to the appropriate layer and set to 'true'.

Data is saved in the following order:

* object type ID (optional, see below)
* row
* col
* width (optional)
* height (optional)

### Saving object type as an ID ###

You can map object types in Tiled to an ID that can be used in code. This will be saved as the first
value in the code. To do this, you must define each object type in Tiled's Object Types Editor
and give each object a unique 'index' custom value.

Export the objecttypes.xml and specify the path using the --object-types parameter.

### RLE Compression Format

1 byte for tilenum, 1 byte for run-length.

The data will be preceded by 2 bytes specifying the array length (hi/lo). This will appear after rows and columns if --add-dimensions is specified.


### Planned Features

* Support for ZX0 compression (ZX0 data compressor by Einar Sukas)
* Creation of ZX Spectrum Next assets (possible)

### Known Issues:

* Leaving gaps in the middle of tilesets (ie. no paper/ink/bright properties) will cause errors.
