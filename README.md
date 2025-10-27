# Spectrum Asset Maker
## Chris Owen 2025

Command-line utility for the creation of a wide range of ZX Spectrum assets for use with z88dk and sp1. Intended to be a one-stop shop for all your game assets.

## Output Formats for Classic and Next

* Tileset Attributes - Game properties (solid, lethal, ladder, custom)

* Path Map - an array showing exits up/down/left/right for each square. Can be used to speed up pathfinding for complex AI.

* Object Map - object type, x, y, width, height

* Text data

* Blank (zeroed) data

* Arrays of data taken from a JSON config file


## Output Formats for Classic

* Tilemap - tile numbers, associated with a tileset

* Tile/Attribute Graphics

* Tile/Attribute Colours

* Masked/Unmasked Sprite (sp1 format)

* .SCR file (eg. loading screen)


## Output Formats for Spectrum Next

* Tile/Attribute Graphics

* Next tilemap - 1 byte per tile format

* Palette - 1 byte per entry format

* Palette - 2 bytes per entry format


## Future Output Formats

* Next tilemap - 2 bytes per tile format

* Next bitmaps

* Next sprites


## Input formats:

|Type|File expected|
|---|---|
|**Tilemap**|Tiled tilemap exported JSON (.tmj)||
|**Tileset**|Tiled tileset exported as JSON (.tsj)|
|**Object types**|Tiled Object Types XML file (.xml)|
|**Tile/Attribute graphics**|Black and white PNG or GIF (PNG recommended)|
|**Next Tile/Attribute graphics**|Indexed PNG or GIF (PNG recommended)|
|**Next Palette**|Indexed PNG or GIF (PNG recommended)|
|**Classic Sprite**|Black and white PNG or GIF (PNG recommended)|
|**Classic Sprite Mask**|Black and white PNG or GIF (PNG recommended)|
|**Text**|Plain text file|
|**Classic SCR**|PNG file, 256 pixels by 192 pixels|

## Installation:

Install PHP with your favourite package manager:

Mac:

> brew install php

Linux - Debian/Ubuntu/Mint:

> sudo apt install php

Linux - Fedora/RedHat:

> sudo dnf install -y php

Windows:

Use installer from PHP.net


## Usage without parameters

On the command line, run:

> php SpectrumAssetMaker.php

## Read a JSON configuration file:

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
    "graphics-next": [{
        "name": "next-font",
        "input": "raw-assets/fonts/lander-bold-next.png",
        "output-folder": "./assets",
        "format": "binary"
    }],
    "palette-next-one-byte": [{
        "name": "next-font",
        "input": "raw-assets/fonts/lander-bold-next.png",
        "output-folder": "./assets",
        "format": "binary"
    }],
    "palette-next-two-bytes": [{
        "name": "next-font",
        "input": "raw-assets/fonts/lander-bold-next.png",
        "output-folder": "./assets",
        "format": "binary"
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

### Known Issues:

* Leaving gaps in the middle of tilesets (ie. no paper/ink/bright properties) will cause errors.
