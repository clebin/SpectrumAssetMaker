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

**--ignore-hidden-layers** (don't process hidden layers. Hidden layers are processed by default)

**--layer-type**=['all', 'objectgroup' or 'tilelayer'] (set which type of Tiled layers to process)

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



### Importing Tilemap layers ###

If --layer-type is set to 'all' (default) or 'tilelayer', the tool will create code for each tilemap layer. The tool will ignore any layers that are not set to 'visible'.

The layer name will be used for variable and file naming, unless --name is specified.

### Importing Object layers ###

If --layer-type is set to 'all' (default) or 'objectgroup', the tool will create code for each objectgroup layer.

You must define each object type in Tiled's Object Types Editor and give each object a unique 'index' custom value. Export the objecttypes.xml and set the path using the --object-types parameter.

### RLE Compression Format

1 byte for tilenum, 1 byte for run-length.

The data will be preceded by 2 bytes specifying the array length (hi/lo). This will appear after rows and columns if --add-dimensions is specified.

### Known Issues:

* Currently tilesets and tilemaps must be exported as JSON (.tsj and .tmj files)

* Don't leave gaps in the middle of tilesets as this will cause errors.

* This tool is work-in-progress and features are currently in flux.
