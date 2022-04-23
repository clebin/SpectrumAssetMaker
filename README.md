Utility to read  tilesets and tilemaps from tiled along with a black & white gif and create Spectrum code.

Chris Owen 2022

** Known Issues: **

* Currently tilesets and tilemaps must be exported as JSON (.tsj and .tmj files)

* Don't leave gaps in the middle of tilesets as this will cause errors.

* This tool is work-in-progress.

**Usage:**

> php SpecTiledTool.php

Running the script without parameters will prompt for each setting.

***Parameters:***

--prefix=<prefix for naming variables>

--outputfolder=<folder path to place generated files>

--map=<tilemap filename>

--tileset=<tileset filename>

--graphics=<tileset graphics filename (black & white png or gif)>

--sprite=<sprite filename>

--mask=<sprite mask filename>

--sprite-width=<sprite width in 8 pixel columns>

--format=<c|asm, default: asm>

--section=<assembly section to place code into, default: rodata_user>

--compression <use RLE compression on tilemaps.>

***RLE Compression Format***

1 byte for tilenum, 1 byte for run-length.

First two bytes contain the array length in bytes (hi/lo).

