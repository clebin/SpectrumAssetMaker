Utility to read  tilesets and tilemaps from tiled along with a black & white gif and create Spectrum code.

Chris Owen 2022

** Known Issues: **

* Currently tilesets and tilemaps must be exported as JSON (.tsj and .tmj files)

* Don't leave gaps in the middle of tilesets as this will cause errors.

* This tool is work-in-progress. Only BASIC export has been tested, and not thoroughly.

**Usage:**

> php SpecTiledTool.php

***Parameters:***

--prefix=<prefix for naming variables>

--map=<tilemap filename>

--tileset=<tileset filename>

--graphics=<tileset graphics filename (black & white png or gif)>

--format=<basic|c|asm, default: asm>

Running the script without parameters will prompt for each setting.
