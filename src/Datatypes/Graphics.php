<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class Graphics extends Datatype
{
    public $datatypeName = 'Graphics';
    protected $image = false;
    public $numColumns = 0;
    public $numRows = 0;
    public $numTiles = 0;
    public $extension = 'png';
    public $paperColour = App::COLOUR_BLACK;

    public function __construct($config)
    {
        parent::__construct($config);

        // paper colour
        if (isset($config['paper-colour']) && in_array($config['paper-colour'], App::$coloursSupported)) {
            $this->paperColour = $config['paper-colour'];
        }

        // set input file
        if ($this->inputFilepath === false) {

            $this->isValid = false;
            App::AddError($this->datatypeName . ': No input specified for "' . $this->name . '"');
            return;
        }

        $this->isValid = $this->ReadFile($this->inputFilepath);
    }

    /**
     * Read a black & white PNG or GIF file
     */
    public function ReadFile($filename)
    {
        if (!file_exists($filename)) {
            App::AddError('Graphics file (' . $filename . ') not found');
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

        if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
            App::OutputMessage($this->datatypeName, $this->name, 'Reading ' . $this->extension . ' file');
        }

        // divide width and height into 8x8 pixel attributes      
        $dimensions = getimagesize($filename);

        $this->numColumns = $dimensions[0] / 8;
        $this->numRows = $dimensions[1] / 8;
        $this->numTiles = $this->numColumns * $this->numRows;

        if (App::GetVerbosity() != App::VERBOSITY_NORMAL) {
            echo 'Graphics: Added' . $this->numTiles . ' attributes';
        } else if (App::GetVerbosity() == App::VERBOSITY_VERBOSE) {
            echo 'Graphics: Added' . $this->extension . ' - ' .
                $this->numColumns . ' x ' . $this->numRows .
                ' attributes (' . $dimensions[0] . ' x ' . $dimensions[1] . 'px) = ' .
                $this->numTiles . ' attributes. ' . CR;
        }

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

                if ($this->extension == 'gif') {

                    if ($rgb == 0) {
                    } else {
                    }
                }

                // transparent counts as paper, or black or white depending on setting
                if (App::ColourIsPaper($rgb, $this->paperColour, $this->extension) === true) {
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
     * Write out data in binary file
     */
    public function WriteBinaryFile($filename)
    {
        $data = $this->GetData();

        // clear file
        file_put_contents($filename, '');

        // add data
        if ($fp = fopen($filename, 'a')) {

            $count = 0;
            foreach ($data as $attribute) {

                foreach ($attribute as $datarow) {
                    $byte = intval(implode('', $datarow));
                    fwrite($fp, pack("C", $byte));
                }
                $count++;
            }

            App::OutputMessage($this->datatypeName, $this->name, 'Wrote ' . $count . ' bytes to binary file.');
        }
    }

    /**
     * Return graphics in assembly format
     */
    public function GetCodeAsm()
    {
        $str = $this->GetHeaderAsm();
        $str .= 'public ' . $this->codeName . CR . CR;
        $str .= '.' . $this->codeName . CR;

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
