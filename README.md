# Spectrum Tiled Tool
## Chris Owen 2022

Utility to to create z88dk/Sp1 compatible screens, tilesets and sprites in assembly or C format from GIFs and Tiled source files.

## Input formats required:

**Tilemap** - Tiled JSON tilemap (.tmj)

**Tileset** - Tiled JSON tileset (.tsj)

**Tileset graphics** - Black and white GIF

**Sprite** - Black and white GIF

**Sprite Mask** - Black and white GIF


## Usage:

> php SpecTiledTool.php

Running the script without parameters will prompt for each setting.


### General Parameters:

**--prefix**=[prefix for naming variables]

**--output-folder**=[folder path to place generated files]

**--format**=['c' or 'asm', default: asm]

**--section**=[assembly section to place code into, default: rodata_user]


### Parameters for Tilemap/Tileset Processing:

**--use-layer-names** (use tilemap layer or layer group name as file and variable names)

**--replace-flash-with-solid** (use the bit normally used for flash to denote a solid block)

**--create-binaries-lst** (create a binaries.lst file all screens and tileset files included - ignored for sprite output)

**--map**=[tilemap filename]

**--tileset**=[tileset filename]

**--graphics**=[tileset graphics filename]

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

**Work-in-progress:** Tilemap layers can be organised into groups to import extra screen data. In this case, each group should contain a Tilemap layer called 'tilemap', and may optionally include Object Layers called 'colours', 'enemies' and 'properties'. Objects in these layers will be imported into their own arrays.

The purpose of the 'colours' and 'properties' layers is to override the default colours and properties set on the tileset to add more variety to your screens.


### RLE Compression Format

1 byte for tilenum, 1 byte for run-length.

First two bytes contain the array length in bytes (hi/lo).

### Known Issues:

* Currently tilesets and tilemaps must be exported as JSON (.tsj and .tmj files)

* Don't leave gaps in the middle of tilesets as this will cause errors.

* This tool is work-in-progress.

