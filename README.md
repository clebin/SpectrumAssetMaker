# Spectrum Asset Maker
## Chris Owen 2025

A command-line utility for the creation of a wide range of ZX Spectrum assets (for Classic and Next). Spectrum Asset Maker is intended to be a one-stop shop for all your game assets.

## Output Formats for Classic and Next

* Tileset Attributes - Game properties (solid, lethal, ladder, custom)

* Path Map - an array showing exits up/down/left/right for each square. Can be used to speed up pathfinding for complex AI.

* Object Map - object type, x, y, width, height

* Text data

* Blank (zeroed) data

* Arrays of data taken from a JSON config file


## Output Formats for Classic Spectrum

* Tilemap - tile numbers, associated with a tileset

* Tile/Attribute Graphics

* Tile/Attribute Colours

* Masked/Unmasked Sprite (sp1 format)

* .SCR file (eg. loading screen)


## Output Formats for Spectrum Next (experimental)

* Tile/Attribute Graphics (nxt)

* Next tilemap (nxt) - short format, 1-byte per tile

* Next tilemap (nxt) - extended format, 2-bytes per tile (palette offset not supported yet)

* Palette (pal) - short format, 1-byte per entry

* Palette (pal) - extended format, 2-bytes per entry

* Sprite (spr) - 4-bit or 8-bit format

* Layer 2 Screen (nxi) - 256x192 with or without embedded palette

## Input formats:

|Type|File format expected|
|---|---|
|**Tilemap**|Tiled tilemap exported JSON (.tmj)||
|**Tileset**|Tiled tileset exported as JSON (.tsj)|
|**Object types**|Tiled Object Types XML file (.xml)|
|**Tile/Attribute graphics**|Black and white PNG or GIF (PNG recommended)|
|**Next Tile/Attribute graphics**|Indexed PNG or GIF (PNG recommended)|
|**Next Palette**|Indexed PNG or GIF (PNG recommended)|
|**Classic Sprite**|Black and white PNG or GIF (PNG recommended)|
|**Classic Sprite Mask**|Black and white PNG or GIF (PNG recommended)|
|**Next Sprite**|Indexed PNG or GIF (PNG recommended)|
|**Text**|Plain text file|
|**Classic SCR**|PNG file, 256 pixels by 192 pixels|
|**Next Layer2 screen**|Indexed PNG file, 256 pixels by 192 pixels|

## Installation:

Install PHP with your favourite package manager:

**Mac with Homebrew:**

> brew install php

**Linux - Debian/Ubuntu/Mint:**

> sudo apt install php

**Linux - Fedora/RedHat:**

> sudo dnf install -y php

**Windows/Mac without Homebrew:**

> (use installer from PHP.net)


## Usage

On the command line, run:

> php SpectrumAssetMaker.php --config=[path to JSON config file]

All assets associated with a project with a project - sprites, tilemaps, graphics etc - are specified in the JSON config file. These are created in one pass, simplifying the build process.

You can pass '--section=[section name]' to only create assets of a particular type as specified in the JSON file.

**Example:**

> php SpectrumAssetMaker.php --config=config-assets.json --section=sprite


## JSON global settings

#### create-assets-list

If the 'create-assets-list' setting is used, the tool will create an 'assets.lst' file in the specified output folder containing file-paths for all the generated assets. You can add this
file to your project settings with '@output-folder/assets.lst' to include the assets as part of your build.

You can exclude individual assets from the LST file using by setting 'add-to-assets-list' to false in the asset's JSON section.

### Naming ###

You can set the format of variable names to match your coding style. Iinclude the "settings/naming" field in your JSON. Options are:

* camelcase (default)

* underscores

* titlecase


## JSON asset settings

#### binary-format

Some Spectrum Next assets can be stored in different binary formats, eg. palettes can be 1 or 2 bytes per colour, and sprites can be 4-bit or 8-bit per-pixel. This is set using the 'binary-format' option (see example JSON for usage)

### create-binary-reference-file

By default, Spectrum Asset Maker creates an asm file pointing to the binary that you can use in your C program, for example:

```
section rodata_user

public _gunsight
public _gunsight_end

_gunsight:

        BINARY "./binary/generated/gunsight.spr" ; 128 bytes

_gunsight_end:
```

If this is unwanted (if you're using Next or Boriel BASIC for example), you can disable this on a per asset-basis by setting 'create-binary-reference-file' to false (see example JSON)

## Example JSON Config File

Below is an example JSON configuration file. More JSON files are included in the 'sample' folder.

```json
{
    "settings": {
        "create-assets-list": true,
        "naming": "underscores",
        "output-folder": "./assets",
        "object-types": "raw-assets/objects/objecttypes.xml"
    },
    "blank-data": [{
        "name": "level-tilemap",
        "size": 2208,
        "output-folder": "./assets/blank-data"
    }],
    "graphics": [{
            "name": "font",
            "input": "raw-assets/fonts/nice-font.png",
            "paper-colour": "white",
            "output-folder": "./assets",
            "section": "BANK_0"
        }],
    "palette-next": [{
        "name": "next-font",
        "input": "raw-assets/fonts/nice-font.png",
        "output-folder": "./assets",
        "format": "binary",
        "binary-format": "1-byte"
        },
        {
            "name": "next-font",
            "input": "raw-assets/fonts/nice-font.png",
            "output-folder": "./assets",
            "format": "binary",
            "binary-format": "2-byte",
            "create-binary-reference-file": false
        },
        {
            "name": "next-font-for-reference",
            "input": "raw-assets/fonts/lander-bold-next.png",
            "output-folder": "./assets/reference-asm",
            "format": "asm",
            "binary-format": "1-byte"
            "add-to-assets-list": false,
        }
    ],
    "screen": [
        {
            "name": "loading-screen",
            "input": "raw-assets/loading-screen.png",
            "output-folder": "./assets"
        }
    ],
    "screen-next-layer2": [
        {
            "name": "background-screen",
            "input": "raw-assets/background-screen.png",
            "output-folder": "./assets",
            "add-palette": true
        }
    ],
    "sprite": [
        {
            "name": "player-sprite",
            "input": "raw-assets/sprites/player-sprite.png",
            "mask": "raw-assets/sprites/player-sprite-mask.png",
            "paper-colour": "black",
            "output-folder": "./assets/sprites",
            "section": "BANK_0"
        }],
    "sprite-next": [
        {
            "name": "player-sprite",
            "input": "raw-assets/sprites/player-sprite.png",
            "output-folder": "./assets/sprites",
            "format": "binary",
            "binary-format": "4-bit"
        },
        {
            "name": "enemy-sprite",
            "input": "raw-assets/sprites/player-sprite.png",
            "output-folder": "./assets/sprites",
            "format": "binary",
            "binary-format": "8-bit"
        }],
    "text": [{
		"name": "intro-text",
		"input": "raw-assets/intro.txt",
		"output-folder": "./assets/text",
	    "section": "BANK_0"
    }],
    "tile-graphics-next": [{
        "name": "next-font",
        "input": "raw-assets/fonts/game-tileset.png",
        "output-folder": "./assets",
        "format": "binary"
    },
    "tilemap": [{
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
    "tileset": [{
        "name": "menu-tiles",
        "tileset": "raw-assets/main-menu/menu-tiles.tsj",
        "output-folder": "./assets/main-menu",
        "section": "BANK_4"
    }]]
}
```

### Tileset format ###

**Classic Spectrum**

Set the following properties in Tiled which get saved in the tileset colours array.

* flash (boolean)

* bright (boolean)

* paper (number 0-7)

* ink (number 0-7)

* solid (boolean)

By default, it will output the attributes as a byte in the usual Spectrum F,B,P3,P2,P1,I3,I2,I1 format. By setting 'replace-flash-with-solid' to true, you can use the most significant bit to store whether the tile is solid instead of FLASH. If only Sinclair had used that precious bit for something more useful (bright paper + bright ink, how much nicer would that have been?)

**Classic or Next:**

You can use the following properties in Tiles to create a separate properties array:

* solid (boolean)

* lethal (boolean)

* ladder (boolean)

* custom (boolean)

This feature may be expanded later.


**Note:** Each tile in the tileset MUST have at least one property set, even if not used by Spectrum Asset Maker, otherwise Tiled won't include it in the exported JSON and you'll end up with missing tiles and errors.


### Importing Tilemap layers ###

If 'layer-type' is set to 'all' (default) or 'tilelayer', the tool will create code for each tilemap layer. The tool will include hidden layers unless 'ignore-hidden-layers' is set to true.

The Tiled layer names will be used for variable and file naming.


### Generating path maps

'Path maps' can be pre-generated which helps speed up movement calculations in some situations. The feature was created to speed up enemy AI in my game Gilligan's Mine.

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

If 'layer-type' is set to 'all' (default) or 'objectgroup', the tool will create code for each objectgroup layer.

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

The data will be preceded by 2 bytes specifying the array length (hi/lo). This will appear after rows and columns if 'add-dimensions' is specified.

### ZX0 Compression (experimental)

You can set 'compression' to 'zx0' to compress a generated binary file using ZX0. This still needs to be fully tested.
