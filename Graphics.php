<?php
namespace ClebinGames\SpecScreenTool;

class Graphics
{
    public $image = false;

    public $data = [];

    public $columns = 0;
    public $rows = 0;
    public $numTiles = 0;

    public static function ReadFile($filename)
    {
        if(!file_exists($filename)) {
            SpecScreenTool::AddError('Graphics file not found');
            return false;
        }

        // divide width and height into 8x8 pixel attributes      
        $dimensions = getimagesize($filename);

        self::$columns = $dimensions[0]/2;
        self::$rows = $dimension[1]/2;

        self::$numTiles = $tilesWidth * $tilesHeight;


        // loop through rows of atttributes
        for($row=0;$row<self::$rows;$row++) {

            // loop through columns of atttributes
            for($col=0;$col<self::$cols;$col++) {
                self::$data[] = self::GetAttributeData($col, $row);
            }
        }
    }
    
    /**
     * Read an individual attribute (or tile)
     */
    public static function GetAttributeData($col, $row)
    {
        // starting values for x & y
        $startx = $col * 8;
        $starty = $row * 8;

        $attribute = [];

        // rows
        for($y=$starty;$y<$starty+8;$y++) {

            $datarow = [];
            // cols
            for($x=$startx;$x<$startx+8;$x++) {

                $rgb = imagecolorat(self::$image, $x, $$y);

                // get rgb values
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // pure black counts as ink
                if( $r == 0 && $g == 0 && $b == 0 ) {
                    $pixel = 1;
                }
                // anything else is paper
                else {
                    $pixel = 0;
                }

                // add pixel value to this row
                $datarow[] = $pixel;
            }
            
            // add row of data
            $attribute[] = $datarow;
        }

        return $attribute;
    }
}
