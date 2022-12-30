<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class Graphics extends Datatype
{
    protected $image = false;
    public $numColumns = 0;
    public $numRows = 0;
    public $numTiles = 0;
    public $extension = 'gif';

    /**
     * Read a black & white PNG or GIF file
     */
    public function ReadFile($filename)
    {
        if (!file_exists($filename)) {
            App::AddError('Graphics file not found');
            return false;
        }

        // read image file
        $this->extension = substr($filename, -3);

        if ($this->extension == 'png') {
            $this->image = imagecreatefrompng($filename);
        } else if ($this->extension == 'gif') {
            $this->image = imagecreatefromgif($filename);
        } else {
            App::AddError('Filetype (' . $this->extension . ') not supported');
            return false;
        }

        // divide width and height into 8x8 pixel attributes      
        $dimensions = getimagesize($filename);

        $this->numColumns = $dimensions[0] / 8;
        $this->numRows = $dimensions[1] / 8;
        $this->numTiles = $this->numColumns * $this->numRows;

        echo 'Tileset graphics (' . $this->extension . '): ' .
            $this->numColumns . ' x ' . $this->numRows .
            ' attributes (' . $dimensions[0] . ' x ' . $dimensions[1] . 'px) = ' .
            $this->numTiles . ' attributes. ' . CR;

        // loop through rows of atttributes
        for ($row = 0; $row < $this->numRows; $row++) {

            // loop through columns of atttributes
            for ($col = 0; $col < $this->numColumns; $col++) {
                $this->data[] = $this->GetPixelData($col, $row);
            }
        }

        return true;
    }

    /**
     * Get raw tile data for a numbered tile
     */
    public function GetTileData($num)
    {
        if (isset($this->data[$num])) {
            return $this->data[$num];
        } else {
            return false;
        }
    }

    /**
     * Read an individual attribute (or tile)
     */
    private function GetPixelData($col, $row)
    {
        // starting values for x & y
        $startx = $col * 8;
        $starty = $row * 8;

        $attribute = [];

        // rows
        for ($y = $starty; $y < $starty + 8; $y++) {

            $datarow = [];

            // cols
            for ($x = $startx; $x < $startx + 8; $x++) {

                $rgb = imagecolorat($this->image, $x, $y);

                // transparent counts as paper, or black or white depending on setting
                if (
                    ($this->extension == 'gif' && $rgb == 1) ||
                    ($this->extension == 'png' && App::colourIsPaper($rgb) === true)
                ) {
                    $pixel = 0;
                }
                // anything else is ink
                else {
                    $pixel = 1;
                }

                // add pixel value to this row
                $datarow[] = $pixel;
            }

            // add row of data
            $attribute[] = $datarow;
        }

        return $attribute;
    }

    /**
     * Return tile graphics in C format
     */
    public function GetCodeC()
    {
        $str = '';

        $str .= '#define ' . $this->defineName . ' ' . sizeof($this->data) . CR . CR;
        $str .= 'const unsigned char ' . $this->codeName . '[' . sizeof($this->data) . '][8] = {' . CR;

        // loop through individual graphics
        $attrcount = 0;
        foreach ($this->data as $attribute) {

            // new line
            if ($attrcount > 0) {
                $str .= ',' . CR;
            }

            $str .= '    {';

            // loop through pixel rows
            $rowcount = 0;
            foreach ($attribute as $datarow) {
                if ($rowcount > 0) {
                    $str .= ',';
                }
                $val = implode('', $datarow);
                $str .= '0x' . dechex(bindec($val));
                $rowcount++;
            }
            $str .= '}';

            $attrcount++;
        }

        $str .= CR . '};' . CR;

        return $str;
    }

    /**
     * Return tile graphics in assembly format
     */
    public function GetCodeAsm()
    {
        $str = 'SECTION ' . App::GetCodeSection() . CR . CR;

        $str .= 'PUBLIC _' . $this->codeName . CR . CR;

        $str .= '._' . $this->codeName . CR;


        foreach ($this->data as $attribute) {

            $count = 0;
            // loop through rows
            foreach ($attribute as $datarow) {
                $str .= 'defb @' . implode('', $datarow) . CR;
                $count++;
            }
            $str .= CR;
        }

        return $str;
    }
}
