# Spectrum Tiled Tool
## Chris Owen 2022

Utility to to create z88dk/Sp1 compatible screens, tilesets and sprites in assembly or C format from GIFs and Tiled source files.

## Input formats required:

**Tilemap** - Tiled JSON tilemap (.tmj)

**Tileset** - Tiled JSON tileset (.tsj)

**Object types** - Tiled Object Types XML file (.xml)

**Tileset graphics** - Black and white GIF

**Sprite** - Black and white GIF

**Sprite Mask** - Black and white GIF


## Usage:

> php SpecTiledTool.php

Running the script without parameters will prompt for each setting.


### General Parameters:

**--name**=[name for output - this option overrides layer names for tile/object maps]

**--output-folder**=[folder path to place generated files]

**--format**=['c' or 'asm', default: asm]

**--section**=[assembly section to place code into, default: rodata_user]


### Parameters for Tilemap/Object Map/Tileset Processing:

**--replace-flash-with-solid** (use the bit normally used for flash to denote a solid block)

**--create-binaries-lst** (create a binaries.lst file all screens and tileset files included - ignored for sprite output)

**--object-types**=[object types XML file] (this is required for processing object maps)

**--map**=[tilemap filename]

**--tileset**=[tileset filename]

**--graphics**=[tileset graphics filename]

**--add-dimensions** (add rows & columns, as the first two elements in the tilemap data arrays)

**--compression**=rle [enable RLE compression on tilemaps]


## Parameters for Sprite Processing:

**--sprite**=[sprite filename]

**--mask**=[sprite mask filename]

**--sprite-width**=[sprite width in 8 pixel columns]


### Tileset format ###

Each tile in your tileset should have the following custom properties set:

* flash (boolean)

* bright (boolean)

* paper (number 0-7)

* ink (number 0-7)

* solid (boolean)

* lethal (boolean)

* platform (boolean)



### Tilemap format ###

The tool will create a separate screen for each tilemap layer. The tool will ignore any layers that are not set to 'visible'.

The layer name will be used for variable and file naming, unless --name is specified.

### RLE Compression Format

1 byte for tilenum, 1 byte for run-length.

The data will be preceded by 2 bytes specifying the array length (hi/lo). This will appear after rows and columns if --add-dimensions is specified.

### Known Issues:

* Currently tilesets and tilemaps must be exported as JSON (.tsj and .tmj files)

* Don't leave gaps in the middle of tilesets as this will cause errors.

* This tool is work-in-progress and features are currently in flux.
