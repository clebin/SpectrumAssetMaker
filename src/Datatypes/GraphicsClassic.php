<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class GraphicsClassic extends Graphics
{
    public string $datatypeName = 'Graphics';
    public string $paperColour = App::COLOUR_BLACK;

    public function __construct($config)
    {
        parent::__construct($config);

        // paper colour
        if (isset($config['paper-colour']) && in_array($config['paper-colour'], App::$coloursSupported)) {
            $this->paperColour = $config['paper-colour'];
        }
    }

    /**
     * Read an individual attribute (or tile)
     */
    public function GetPixelData($col, $row) : array
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

                if ($this->extension == App::FILE_EXTENSION_GIF) {

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
    public function WriteBinaryFile($filename) : void
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