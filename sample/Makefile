TOOLPATH = ../SpectrumAssetMaker.php

all: clean config-all

clean:
	-rm ./output/asm/blank-data/*.asm
	-rm ./output/asm/graphics/*.asm
	-rm ./output/asm/graphics-next/*.asm
	-rm ./output/asm/music/*.asm
	-rm ./output/asm/object-maps/*.asm
	-rm ./output/asm/sprites/*.asm
	-rm ./output/asm/sprites-next/*.asm
	-rm ./output/asm/palettes-next/*.asm
	-rm ./output/asm/text/*.asm
	-rm ./output/asm/tilemaps/*.asm
	-rm ./output/asm/tilesets/*.asm
	-rm ./output/asm/*.asm
	-rm ./output/c/blank-data/*.c
	-rm ./output/c/graphics/*.c
	-rm ./output/c/music/*.c
	-rm ./output/c/asm/sprites/*.c
	-rm ./output/c/text/*.c
	-rm ./output/c/tilemaps/*.c
	-rm ./output/c/tilesets/*.c
	-rm ./output/c/*.c
	-rm ./output/binary/*.zx0
	-rm ./output/binary/tilesets/*.zx0
	-rm ./output/binary/tilemaps/*.zx0
	-rm ./output/binary/text/*.zx0
	-rm ./output/binary/blank-data/*.zx0
	-rm ./output/binary/music/*.zx0
	-rm ./output/binary/graphics/*.zx0
	-rm ./output/binary/graphics-next/*.zx0
	-rm ./output/binary/object-maps/*.zx0
	-rm ./output/binary/sprites/*.zx0
	-rm ./output/binary/graphics-next/*.nxt
	-rm ./output/binary/graphics-next/*.nxi
	-rm ./output/binary/palettes-next/*.pal
	-rm ./output/binary/sprites-next/*.spr
	-rm ./output/binary/graphics-next/*.nxm
	
config-all:
	php $(TOOLPATH) --config=./config.json

blank-data:
	php $(TOOLPATH) --config=./config.json --datatype=blank-data

sprite:
	php $(TOOLPATH) --config=./config.json --section=sprite

sprite-next-4bit:
	php $(TOOLPATH) --config=./config.json --section=sprite-next-4bit

sprite-next-8bit:
	php $(TOOLPATH) --config=./config.json --section=sprite-next-8bit

graphics:
	php $(TOOLPATH) --config=./config.json --section=graphics

graphics-next:
	php $(TOOLPATH) --config=./config.json --section=tile-graphics-next

palette-next-one-byte:
	php $(TOOLPATH) --config=./config.json --section=palette-next-one-byte

palette-next-two-bytes:
	php $(TOOLPATH) --config=./config.json --section=palette-next-two-bytes

screen-next-layer2:
	php $(TOOLPATH) --config=./config.json --section=screen-next-layer2

text:
	php $(TOOLPATH) --config=./config.json --section=text

tilemap:
	php $(TOOLPATH) --config=./config.json --section=tilemap

array-data:
	php $(TOOLPATH) --config=./config.json --section=array-data
