TOOLPATH = ../SpectrumAssetMaker.php

all: clean config-all

clean:
	-rm ./output/asm/blank-data/*.asm
	-rm ./output/asm/blank-data/*.zx0
	-rm ./output/asm/graphics/*.asm
	-rm ./output/asm/graphics/*.zx0
	-rm ./output/asm/music/*.asm
	-rm ./output/asm/music/*.zx0
	-rm ./output/asm/object-maps/*.asm
	-rm ./output/asm/object-maps/*.zx0
	-rm ./output/asm/sprites/*.asm
	-rm ./output/asm/sprites/*.zx0
	-rm ./output/asm/text/*.asm
	-rm ./output/asm/text/*.zx0
	-rm ./output/asm/tilemaps/*.asm
	-rm ./output/asm/tilemaps/*.zx0
	-rm ./output/asm/tilesets/*.asm
	-rm ./output/asm/tilesets/*.zx0
	-rm ./output/asm/*.asm
	-rm ./output/asm/*.zx0
	-rm ./output/c/blank-data/*.c
	-rm ./output/c/graphics/*.c
	-rm ./output/c/music/*.c
	-rm ./output/c/asm/sprites/*.c
	-rm ./output/c/text/*.c
	-rm ./output/c/tilemaps/*.c
	-rm ./output/c/tilesets/*.c
	-rm ./output/c/*.c

config-all:
	php $(TOOLPATH) --config=./config.json

blank-data:
	php $(TOOLPATH) --config=./config.json --datatype=blank-data

sprites:
	php $(TOOLPATH) --config=./config.json --section=sprites

sprites-next-4bit:
	php $(TOOLPATH) --config=./config.json --section=sprites-next-4bit

sprites-next-8bit:
	php $(TOOLPATH) --config=./config.json --section=sprites-next-8bit

graphics:
	php $(TOOLPATH) --config=./config.json --section=graphics

graphics-next:
	php $(TOOLPATH) --config=./config.json --section=tile-graphics-next

palettes-next-one-byte:
	php $(TOOLPATH) --config=./config.json --section=palettes-next-one-byte

palettes-next-two-bytes:
	php $(TOOLPATH) --config=./config.json --section=palettes-next-two-bytes

text:
	php $(TOOLPATH) --config=./config.json --section=text

tilemaps:
	php $(TOOLPATH) --config=./config.json --section=tilemaps

array-data:
	php $(TOOLPATH) --config=./config.json --section=array-data
