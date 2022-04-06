<?php
namespace ClebinGames\SpecTiledTool;

class Sprite
{
    private static $spriteImage = false;
    private static $maskImage = false;

    private static $spriteData = [];
    private static $maskData = [];

    public static $width = 0;
    public static $height = 0;
    public static $numColumns = 0;
    public static $numRows = 0;

    /**
     * Read a black & white PNG or GIF file
     */
    public static function ReadFiles($spriteFile, $maskFile = false)
    {
        self::$spriteImage = self::GetImage($spriteFile);

        if( $maskFile !== false ) {
            self::$maskImage = self::GetImage($maskFile);
        }

        if( SpecTiledTool::DidErrorOccur() === true ) {
            return false;
        }

        // set dimensions for the main sprite
        // divide width and height into 8x8 pixel attributes      
        self::$width = imagesx(self::$spriteImage);
        self::$height = imagesy(self::$spriteImage);
        self::$numColumns = self::$width/8;
        self::$numRows = self::$height/8;

        echo 'Reading sprite: '.self::$numColumns.' x '.self::$numRows.
        ' attributes ('.self::$width.' x '.self::$height.'px)';
        
        // get raw pixel data
        self::$spriteData = self::GetImageData(self::$spriteImage);

        if( $maskFile !== false ) {
            self::$maskData = self::GetImageData(self::$maskImage);
        }
    }
    
    public static function GetImage($filename)
    {
        if(!file_exists($filename)) {
            SpecTiledTool::AddError('File "'.$filename.'" not found');
            return false;
        }
        
        // read image file
        $extension = substr($filename, -3);

        if( $extension == 'png' ) {
            return imagecreatefrompng($filename);
        } else if( $extension == 'gif' ) {
            return imagecreatefromgif($filename);
        } else {
            SpecTiledTool::AddError('Filetype ('.$extension.') not supported');
            return false;
        }
    }

    public static function GetImageData($image)
    {
        // loop through rows of atttributes
        for($row=0;$row<self::$numRows;$row++) {
            // loop through columns of atttributes
            for($col=0;$col<self::$numColumns;$col++) {
                self::$spriteData[] = self::GetPixelData($image, $col, $row);
            }
        }
    }

    /**
     * Read an individual attribute (or tile)
     */
    private static function GetPixelData($image, $col, $row)
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

                $rgb = imagecolorat($image, $x, $y);
                
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
        
        $numBytes = ((sizeof(self::$spriteData)*8*2)+(2*self::$numRows));

        $str .= 'const unsigned char '.SpecTiledTool::GetPrefix().'Sprite['.$numBytes.'] = {'.CR;
        
        // output left padding
        for($i=0;$i<self::$numRows;$i++) {
            if( $i > 0 ) {
                $str .= ', ';
            } 
            $str .= '0x00';
        }

        // get image and mask data
        $count = 0;

        for($i=0;$i<sizeof(self::$spriteData);$i++) {
            for($n=0;$n<8;$n++) {

                $val = implode('', self::$spriteData[$i][$n]);
                $str .= ', 0x'.dechex(bindec($val));

                if( isset(self::$maskData[$i][$n])) {
                    $val = implode('', self::$maskData[$i][$n]);
                    $str .= ', 0x'.dechex(bindec($val));
                } else {
                    $str .= ', 0x0';
                }
            }
        }

        // output right padding
        for($i=0;$i<self::$numRows;$i++) {
            $str .= ', 0x00';
        }

        $str .= CR.'};'.CR;

        return $str;
    }

    /**
     * Return tile graphics in BASIC format
     */
    public static function GetBasic()
    {
        $str = 'Dim '.SpecTiledTool::GetPrefix().'('.(sizeof(self::$spriteData)-1).',7) as uByte => { _'.CR;
        
        // loop through individual graphics
        $attrcount = 0;
        foreach(self::$spriteData as $attribute) {

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
        foreach(self::$spriteData as $attribute) {
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
