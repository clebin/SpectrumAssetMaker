TOOLPATH = ../SpectrumAssetMaker.php

all: asm c

clean:
	-rm ./output/asm/tilemaps/*.asm
	-rm ./output/asm/object-maps/*.asm
	-rm ./output/asm/*.asm
	-rm ./output/c/*.c
	-rm ./output/c/tilemaps/*.c
	-rm ./output/c/object-maps/*.c

# assembly output
asm: tilemap-asm font-asm screens-asm masked-sprite-asm \
	unmasked-sprite-asm object-map-asm blank-data-asm xml-tileset-asm

config-blank-data:
	php $(TOOLPATH) --config=./config-blank-data.json

config-sprites:
	php $(TOOLPATH) --config=./config-sprites.json

config-graphics:
	php $(TOOLPATH) --config=./config-graphics.json

config-text:
	php $(TOOLPATH) --config=./config-text.json

config-tilemaps:
	php $(TOOLPATH) --config=./config-tilemaps.json

config-music:
	php $(TOOLPATH) --config=./config-music.json

text-asm:
	php $(TOOLPATH) \
		--name=sample-text \
		--text=./raw-assets/text.txt \
		--output-folder=./output/asm/text \
		--format=asm

text-c:
	php $(TOOLPATH) \
		--name=sample-text \
		--text=./raw-assets/text.txt \
		--output-folder=./output/c \
		--format=c

blank-data-asm:
	php $(TOOLPATH) \
		--name=empty-data \
		--blank-data=40 \
		--output-folder=./output/asm \
		--format=asm

tilemap-asm:
	php $(TOOLPATH) \
		--name=laundrette \
		--map=./raw-assets/laundrette.tmj \
		--output-folder=./output/asm \
		--format=asm \
		--add-dimensions

xml-tilemap-asm:
	php $(TOOLPATH) \
		--map=./raw-assets/islands.tmx \
		--output-folder=./output/asm/tilemaps \
		--object-types=raw-assets/objects/objecttypes-units.xml \
		--format=asm \
		--add-dimensions

font-asm:
	php $(TOOLPATH) \
		--name=font \
		--graphics=./raw-assets/font.gif \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores

screens-asm:
	php $(TOOLPATH) \
		--name=tileset \
		--use-layer-names \
		--replace-flash-with-solid \
		--tileset=./raw-assets/tileset.tsj \
		--map=./raw-assets/screen-tilemaps.tmj \
		--graphics=./raw-assets/tiles-bw.gif \
		--output-folder=./output/asm/tilemaps \
		--compression=rle \
		--format=asm \
		--naming=underscores

tileset-asm:
	php $(TOOLPATH) \
		--name=tileset \
		--tileset=./raw-assets/tileset.tsj \
		--graphics=./raw-assets/tiles-bw.gif \
		--output-folder=./output/asm/tilemaps \
		--format=asm \
		--naming=underscores

xml-tileset-asm:
	php $(TOOLPATH) \
		--name=tileset \
		--tileset=./raw-assets/tileset.tsx \
		--graphics=./raw-assets/tiles-bw.gif \
		--output-folder=./output/asm/tilemaps \
		--format=asm \
		--naming=underscores

xml-screens-asm:
	php $(TOOLPATH) \
		--name=tileset \
		--use-layer-names \
		--replace-flash-with-solid \
		--tileset=./raw-assets/tileset.tsj \
		--map=./raw-assets/screen-tilemaps.tmx \
		--graphics=./raw-assets/tiles-bw.gif \
		--output-folder=./output/asm/tilemaps \
		--compression=rle \
		--format=asm \
		--naming=camelcase

masked-sprite-asm:
	php $(TOOLPATH) \
		--name=bubble \
		--sprite=./raw-assets/bubble.gif \
		--mask=./raw-assets/bubble-mask.gif \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores

unmasked-sprite-asm:
	php $(TOOLPATH) \
		--name=face \
		--sprite=./raw-assets/face.gif \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores

object-map-asm:
		php $(TOOLPATH) \
		--name=screen-objects \
		--map=./raw-assets/screen-objects.tmj \
		--object-types=./raw-assets/objects/objecttypes.xml \
		--output-folder=./output/asm/object-maps \
		--format=asm \
		--naming=underscores

xml-object-map-asm:
		php $(TOOLPATH) \
		--name=screen-objects \
		--map=./raw-assets/screen-objects.tmx \
		--object-types=./raw-assets/objects/objecttypes.xml \
		--output-folder=./output/asm/object-maps \
		--format=asm \
		--naming=underscores

# c output
c: tilemap-c font-c screens-c masked-sprite-c unmasked-sprite-c object-map-c

tilemap-c:
	php $(TOOLPATH) \
		--name=laundrette \
		--map=./raw-assets/laundrette.tmj \
		--output-folder=./output/c \
		--format=c \
		--add-dimensions

font-c:
	php $(TOOLPATH) \
		--name=font \
		--graphics=./raw-assets/font.gif \
		--output-folder=./output/c \
		--format=c

screens-c:
	php $(TOOLPATH) \
		--name=tileset \
		--use-layer-names \
		--replace-flash-with-solid \
		--tileset=./raw-assets/tileset.tsj \
		--map=./raw-assets/screen-tilemaps.tmj \
		--graphics=./raw-assets/tiles-bw.gif \
		--output-folder=./output/c/tilemaps \
		--compression=rle \
		--format=c

masked-sprite-c:
	php $(TOOLPATH) \
		--name=bubble \
		--sprite=./raw-assets/bubble.gif \
		--mask=./raw-assets/bubble-mask.gif \
		--output-folder=./output/c \
		--format=c

unmasked-sprite-c:
	php $(TOOLPATH) \
		--name=face \
		--sprite=./raw-assets/face.gif \
		--output-folder=./output/c \
		--format=c \
		--naming=titlecase

object-map-c:
	php $(TOOLPATH) \
		--name=screen-objects \
		--map=./raw-assets/screen-objects.tmj \
		--object-types=./raw-assets/objects/objecttypes.xml \
		--output-folder=./output/c/object-maps \
		--format=c

xml-tilemap-c:
	php $(TOOLPATH) \
		--map=./raw-assets/islands.tmx \
		--output-folder=./output/c/tilemaps \
		--object-types=raw-assets/objects/objecttypes-units.xml \
		--format=c \
		--add-dimensions
