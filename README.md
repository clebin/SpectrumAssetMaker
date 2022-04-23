# Spectrum Tiled Tool
## Chris Owen 2022

Utility to to create z88dk/Sp1 compatible screens, tilesets and sprites. 

## Input formats required:

**Tilemap** - Tiled JSON tilemap (.tmj)

**Tileset** - Tiled JSON tileset (.tsj)

**Tileset graphics** - Black and white GIF

**Sprite** - Black and white GIF

**Sprite Mask** - Black and white GIF


## Usage:

> php SpecTiledTool.php

Running the script without parameters will prompt for each setting.


### Parameters:

**--prefix**=<prefix for naming variables>

**--outputfolder**=<folder path to place generated files>

**--map**=<tilemap filename)>

**--tileset**=<tileset filename>

**--graphics**=<tileset graphics filename>

**--sprite**=<sprite filename>

**--mask**=<sprite mask filename>

**--sprite-width**=<sprite width in 8 pixel columns>

**--format**=<'c' or 'asm', default: asm>

**--section**=<assembly section to place code into, default: rodata_user>

**--compression** <enable RLE compression on tilemaps>

### RLE Compression Format

1 byte for tilenum, 1 byte for run-length.

First two bytes contain the array length in bytes (hi/lo).

### Known Issues:

* Currently tilesets and tilemaps must be exported as JSON (.tsj and .tmj files)

* Don't leave gaps in the middle of tilesets as this will cause errors.

* This tool is work-in-progress.

