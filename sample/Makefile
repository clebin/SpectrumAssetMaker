toolpath = ../SpecTiledTool.php

all: asm c

clean:
	rm ./output/asm/screens/*
	rm ./output/asm/*.asm
	rm ./output/c/*.c
	rm ./output/c/screens/*.c

# assembly output
asm: tilemap-asm tileset-asm screens-asm masked-sprite-asm unmasked-sprite-asm

tilemap-asm:
	php $(toolpath) \
		--prefix=laundrette \
		--map=laundrette.tmj \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores

tileset-asm:
	php $(toolpath) \
		--prefix=font \
		--graphics=./font.gif \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores

screens-asm:
	php $(toolpath) \
		--use-layer-names \
		--replace-flash-with-solid \
		--tileset=./tileset.tsj \
		--map=./screens.tmj \
		--graphics=./tiles-bw.gif \
		--output-folder=./output/asm/screens \
		--compression=rle \
		--format=asm \
		--naming=underscores

masked-sprite-asm:
	php $(toolpath) \
		--prefix=bubble \
		--sprite=./bubble.gif \
		--mask=./bubble-mask.gif \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores

unmasked-sprite-asm:
	php $(toolpath) \
		--prefix=face \
		--sprite=./face.gif \
		--output-folder=./output/asm \
		--format=asm \
		--naming=underscores

# c output
c: tilemap-c tileset-c screens-c masked-sprite-c unmasked-sprite-c

tilemap-c:
	php $(toolpath) \
		--prefix=laundrette \
		--map=laundrette.tmj \
		--output-folder=./output/c \
		--format=c

tileset-c:
	php $(toolpath) \
		--prefix=font \
		--graphics=./font.gif \
		--output-folder=./output/c \
		--format=c

screens-c:
	php $(toolpath) \
		--use-layer-names \
		--replace-flash-with-solid \
		--tileset=./tileset.tsj \
		--map=./screens.tmj \
		--graphics=./tiles-bw.gif \
		--output-folder=./output/c/screens \
		--compression=rle \
		--format=c

masked-sprite-c:
	php $(toolpath) \
		--prefix=bubble \
		--sprite=./bubble.gif \
		--mask=./bubble-mask.gif \
		--output-folder=./output/c \
		--format=c

unmasked-sprite-c:
	php $(toolpath) \
		--prefix=face \
		--sprite=./face.gif \
		--output-folder=./output/c \
		--format=c \
		--naming=titlecase

