toolpath = ../SpecTiledTool.php

all: asm c

clean:
	rm ./output/asm/tilemaps/*
	rm ./output/asm/object-maps/*
	rm ./output/asm/*.asm
	rm ./output/c/*.c
	rm ./output/c/tilemaps/*.c
	rm ./output/c/object-maps/*.c

# assembly output
asm: tilemap-asm tileset-asm screens-asm masked-sprite-asm unmasked-sprite-asm object-map-asm

tilemap-asm:
	php $(toolpath) \
		--name=laundrette \
		--map=laundrette.tmj \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores \
		--add-dimensions

tileset-asm:
	php $(toolpath) \
		--name=font \
		--graphics=./font.gif \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores \

screens-asm:
	php $(toolpath) \
		--use-layer-names \
		--replace-flash-with-solid \
		--tileset=./tileset.tsj \
		--map=./screen-tilemaps.tmj \
		--graphics=./tiles-bw.gif \
		--output-folder=./output/asm/tilemaps \
		--compression=rle \
		--format=asm \
		--naming=underscores

masked-sprite-asm:
	php $(toolpath) \
		--name=bubble \
		--sprite=./bubble.gif \
		--mask=./bubble-mask.gif \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores

unmasked-sprite-asm:
	php $(toolpath) \
		--name=face \
		--sprite=./face.gif \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores

object-map-asm:
		php $(toolpath) \
		--map=./screen-objects.tmj \
		--object-types=objecttypes.xml \
		--output-folder=./output/asm/object-maps \
		--format=asm \
		--naming=underscores

# c output
c: tilemap-c tileset-c screens-c masked-sprite-c unmasked-sprite-c object-map-c

tilemap-c:
	php $(toolpath) \
		--name=laundrette \
		--map=laundrette.tmj \
		--output-folder=./output/c \
		--format=c \
		--add-dimensions

tileset-c:
	php $(toolpath) \
		--name=font \
		--graphics=./font.gif \
		--output-folder=./output/c \
		--format=c

screens-c:
	php $(toolpath) \
		--use-layer-names \
		--replace-flash-with-solid \
		--tileset=./tileset.tsj \
		--map=./screen-tilemaps.tmj \
		--graphics=./tiles-bw.gif \
		--output-folder=./output/c/tilemaps \
		--compression=rle \
		--format=c

masked-sprite-c:
	php $(toolpath) \
		--name=bubble \
		--sprite=./bubble.gif \
		--mask=./bubble-mask.gif \
		--output-folder=./output/c \
		--format=c

unmasked-sprite-c:
	php $(toolpath) \
		--name=face \
		--sprite=./face.gif \
		--output-folder=./output/c \
		--format=c \
		--naming=titlecase

object-map-c:
	php $(toolpath) \
		--map=./screen-objects.tmj \
		--object-types=objecttypes.xml \
		--output-folder=./output/c/object-maps \
		--format=c

