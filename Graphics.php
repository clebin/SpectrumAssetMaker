<?php
namespace ClebinGames\SpecTiledTool;

class Graphics
{
    private static $image = false;
    private static $data = [];

    public static $numColumns = 0;
    public static $numRows = 0;
    public static $numTiles = 0;
    
    /**
     * Read a black & white PNG or GIF file
     */
    public static function ReadFile($filename)
    {
        if(!file_exists($filename)) {
            SpecTiledTool::AddError('Graphics file not found');
            return false;
        }

        // read image file
        $extension = substr($filename, -3);

        if( $extension == 'png' ) {
            self::$image = imagecreatefrompng($filename);
        } else if( $extension == 'gif' ) {
            self::$image = imagecreatefromgif($filename);
        } else {
            SpecTiledTool::AddError('Filetype ('.$extension.') not supported');
            return false;
        }

        // divide width and height into 8x8 pixel attributes      
        $dimensions = getimagesize($filename);

        self::$numColumns = $dimensions[0]/8;
        self::$numRows = $dimensions[1]/8;
        self::$numTiles = self::$numColumns * self::$numRows;

        echo 'Reading '.$extension.' image: '.
            self::$numColumns.' x '.self::$numRows.
            ' attributes ('.$dimensions[0].' x '.$dimensions[1].'px) = '.
            self::$numTiles.' attributes. '.CR;

        
        // loop through rows of atttributes
        for($row=0;$row<self::$numRows;$row++) {

            // loop through columns of atttributes
            for($col=0;$col<self::$numColumns;$col++) {
                self::$data[] = self::GetPixelData($col, $row);
            }
        }
    }

    /**
     * Get raw tile data for a numbered tile
     */
    public static function GetTileData($num)
    {

        if( isset(self::$data[$num])) {
            return self::$data[$num];
        } else {
            return false;
        }
    }
    
    /**
     * Read an individual attribute (or tile)
     */
    private static function GetPixelData($col, $row)
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

                $rgb = imagecolorat(self::$image, $x, $y);
                
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

    /**
     * Get tile graphics code in currently set format/language
     */
    public static function GetCode()
    {
        switch( SpecTiledTool::GetFormat() ) {
            case 'basic':
                return self::GetBasic();
                break;
            
            case 'c':
                return self::GetC();
                break;

            default:
                return self::GetAsm();
                break;
        }
    }

    /**
     * Return tile graphics in C format
     */
    public static function GetC()
    {
        $str = '';
        
        $str .= '#define '.strtoupper(SpecTiledTool::GetPrefix()).'_LEN '.sizeof(self::$data).CR.CR;
        $str .= 'const unsigned char '.SpecTiledTool::GetPrefix().'['.sizeof(self::$data).'][8] = {'.CR;
        
        // loop through individual graphics
        $attrcount = 0;
        foreach(self::$data as $attribute) {

            // new line
            if( $attrcount > 0 ) {
                $str .= ','.CR;
            }

            $str .= '    {';

            // loop through pixel rows
            $rowcount = 0;
            foreach($attribute as $datarow) {
                if( $rowcount > 0 ) {
                    $str .= ',';
                }
                $val = implode('', $datarow);
                $str .= '0x'.dechex(bindec($val));
                $rowcount++;
            }
            $str .= '}';

            $attrcount++;
        }

        $str .= CR.'};'.CR;

        return $str;
    }

    /**
     * Return tile graphics in BASIC format
     */
    public static function GetBasic()
    {
        $str = 'Dim '.SpecTiledTool::GetPrefix().'('.(sizeof(self::$data)-1).',7) as uByte => { _'.CR;
        
        // loop through individual graphics
        $attrcount = 0;
        foreach(self::$data as $attribute) {

            // new line
            if( $attrcount > 0 ) {
                $str .= ', _'.CR;
            }

            $str .= '    {';

            // loop through pixel rows
            $rowcount = 0;
            foreach($attribute as $datarow) {
                if( $rowcount > 0 ) {
                    $str .= ',';
                }
                $val = implode('', $datarow);
                $str .= bindec($val);
                $rowcount++;
            }
            $str .= '}';

            $attrcount++;
        }

        $str .= ' _'.CR.'}'.CR;

        return $str;
    }

    /**
     * Return tile graphics in assembly format
     */
    public static function GetAsm()
    {
        $str = '';
        $count = 0;
        foreach(self::$data as $attribute) {
            $str .= '._'.SpecTiledTool::GetPrefix().'_graphics_'.$count.CR;

            // loop through rows
            foreach($attribute as $datarow) {
                $str .= 'defb @'.implode('', $datarow).CR;
            }
            $str .= CR;

            $count++;
        }
        
        return $str;
    }
}
