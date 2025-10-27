<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class GraphicsNext extends Graphics
{
    public string $datatypeName = 'Next Graphics';

    public string $binaryFileExtension = 'nxt';
    public bool $addArrayLength = false;

    protected string $codeFormat = App::FORMAT_BINARY;

    public function __construct($config)
    {
        parent::__construct($config);
    }

    public function ReadAttributes() : array
    {
        $data = [];

        // loop through rows of atttributes
        for ($row = 0; $row < $this->numRows; $row++) {

            // loop through columns of atttributes
            for ($col = 0; $col < $this->numColumns; $col++) {
                $attribute = $this->ReadAttribute($col, $row);

                $data = array_merge($data, $attribute);
            }
        }

        return $data;
    }


    /**
     * Read an individual attribute (or tile)
     */
    public function ReadAttribute($col, $row) : array
    {
        // starting values for x & y
        $startx = $col * 8;
        $starty = $row * 8;

        $attribute = [];

        // rows
        for ($y = $starty; $y < $starty + 8; $y++) {
            
            // cols
            for ($x = $startx; $x < $startx + 8; $x++) {

                $pixelColour1 = imagecolorat($this->image, $x, $y);
                
                if( $pixelColour1 < 0 || $pixelColour1 >= 16) {
                    $pixelColour1 = 0;
                }

                $pixelColour1 = $pixelColour1 << 4;
                

                // next pixel
                $x++;

                $pixelColour2 = imagecolorat($this->image, $x, $y);
                
                if( $pixelColour2 < 0 || $pixelColour2 >= 16) {
                    $pixelColour2 = 0;
                }

                // combine the two into one byte
                $pixelColour = $pixelColour1 | $pixelColour2;
                
                // echo $pixelColour1.' | '.$pixelColour2.' = '.$pixelColour.' ('.decbin($pixelColour).')'.CR;

                // add row of data
                $attribute[] = $pixelColour;
            }
        }

        return $attribute;
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

                foreach ($attribute as $pixel) {

                    $pixel = intval($pixel);
                    fwrite($fp, pack("C", $pixel));

                }
                $count++;
            }

            App::OutputMessage($this->datatypeName, $this->name, 'Wrote ' . $count . ' bytes to binary file.');
        }
    }
}
