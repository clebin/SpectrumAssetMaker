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

* Bitmap (nxi) - pixels stored in row or column order

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


## Example JSON Config File

Here is an example JSON configuration file to create a large set of game assets. The settings are explained in more detail below.


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
    "bitmap-next": [
        {
            "name": "background-screen",
            "input": "raw-assets/background-screen.png",
            "output-folder": "./assets",
            "bank": 30
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
            "section": "BANK_4",
            "custom-properties": {
                "colours": true,
                "properties": [
                    "solid",
                    "lethal",
                    {
                        "name": "prettiness",
                        "length": 6
                    }
                ]
            }
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

## JSON global settings

### create-assets-list

If the 'create-assets-list' setting is used, the tool will create an 'assets.lst' file in the specified output folder containing file-paths for all the generated assets. You can add this
file to your project settings with '@output-folder/assets.lst' to include the assets as part of your build.

You can exclude individual assets from the LST file using by setting 'add-to-assets-list' to false in the asset's JSON section.

### naming

You can set the format of variable names to match your coding style. Iinclude the "settings/naming" field in your JSON. Options are:

* camelcase (default)

* underscores

* titlecase


### next-screen-format

Set how the Next screen is laid out, either as 3 rows (for 256x192 resolution) or 5 columns (for 320x256 or 640x256 resolution).

* rows (default)

* columns


## JSON asset settings

### input

Input file to use

### output-folder

Where to save the generated asset(s)

### output-filename

Out to a specific filename, overriding auto-generated filename.

### format

Format to save data as, usually assembly or as binary file.

* asm (default)

* binary

* c


### binary-format

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


### section

Code/memory section to save the asm to (eg. "rodata_section", "PAGE_80", "BANK_20")


### bank

Alternative to 'section' setting - set memory bank by number


### page

Alternative to 'section' setting - set memory page by number


### compression

Set the compression format to use (see README section on Compression). Default is no compression.

* rle

* zx0 (binary only)


### bitmap-format

*For Next bitmaps*. Override the global "next-screen-format" setting to save by columns or rows.

* rows

* columns


### paper-colour

*For classic Spectrum sprites*. Set which colour to use for PAPER. Anything else will be taken as INK.

### mask

*For classic Spectrum sprites*. Specify a path to an image to use as the sprite mask.

### ignore-hidden-layers

*For tilemaps*. Set to true to skip importing hidden tile layers. Default is false.


### Importing Tilemap layers ###

If 'layer-type' is set to 'all' (default) or 'tilelayer', the tool will create code for each tilemap layer. The tool will include hidden layers unless "ignore-hidden-layers" is set to true.

The destination code name is built from the folder and layer names in Tiled and respects the global "naming" setting. For example, take the following structure in Tiled:

```
(folder) level-1
|_____ (tilelayer) layout
|______(objectmap) enemies
|______(objectmap) collectables
```
This will create the following asm resources. With "naming" set to "underscores":

```
level_1_layout
level_1_enemies
level_1_colletables
```

With "naming" set to "camelcase":
```
level1Layout
level1Enemies
level1Collectables
```

With "naming" set to "titlecase":
```
Level1Layout
Level1Enemies
Level1Collectables
```


### Tileset format ###


By default, Spectrum Asset Maker will output the attributes as a byte in the usual Spectrum format attribute (F,B,P3,P2,P1,I3,I2,I1).

Add the following properties to the individual tile in Tiled:

* flash (boolean)

* bright (boolean)

* paper (number 0-7)

* ink (number 0-7)

* solid (boolean)


### Custom Properties ###

You can customise what is saved by adding a "tile-properties" section to the tileset config JSON. This allows you to create as many 8-bit arrays of properties as you like.

In the example below, we've modified the 'colours' array to replace 'flash' with 'solid' and added a second array with 4 boolean properties (unbreakable, lethal, ladder, water) and a 4-bit integer property (rating):

```json

"tileset-properties": [
{
    "name": "game-tiles",
    "tileset": "raw-assets/bean-bros.tsj",
    "output-folder": "./binary/generated-asm",
    "format": "asm",
    "custom-properties": {
        "colours": [
            "solid", 
            "bright",
            {
                "name": "paper",
                "length": 3
            },
            {
                "name": "ink",
                "length": 3
            }
        ],
        "my-properties": [
            "unbreakable",
            "lethal",
            "ladder",
            "water",
            {
                "name" : "rating",
                "length" : 4
            }
        ]
    }
}
```

You can disable the 'colours' array like this:

```json
"custom-properties": {
    "colours": false,
    "my-properties": [
        ...
    ]
}
```

Or you can add a second array of properties but leave the 'colours' array as default:

```json
"custom-properties": {
    "colours": true,
    "my-properties": [
        ...
    ]
}
```

The resulting arrays for 'tilesetMine' and 'tilesetJungle' would be named in code like this (where "naming" is set to "camelcase"):

```
tilesetMineColours
tilesetMineMyProperties
tilesetJungleColours
tilesetJungleMyProperties
```


**Note:** Each tile in the tileset MUST have at least one property set, even if not used by Spectrum Asset Maker, otherwise Tiled won't include it in the exported JSON and you'll end up with missing tiles and errors.

**Note 2:** At the moment each tile slot uses a full 8-bits, potentialy wasting bits. In future it should be possible to combile multiple tiles into a single byte.


### Generating path maps

'Path maps' can be pre-generated which helps speed up movement calculations in some situations. The feature was created to speed up enemy AI in my game Gilligan's Mine.

This will create a byte for each square on the map that specifies which directions a player/character may move from that square.

This feature requires 'solid' (true/false) and optionally 'ladder' (true/false) properties to be set on tileset tiles in Tiled.


### path-width, path-height

The path-width and path-height settings are used to specify how many tiles wide/high the gap needs to be to fit through.


### path-map-style

The 'path-map-style' property changes navigation style. There are two options:

* platform - character can only move up and down if the tiles have 'ladder' set to true.

* overhead - character can move in all 4 directions if there's a space

### Output format

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
