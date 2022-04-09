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

        echo 'Reading sprite: '.self::$numColumns.' columns ('.self::$width.' x '.self::$height.'px)';
        
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
        $data = [];

        // loop through columns
        for($col=0;$col<self::$numColumns;$col++) {
            $data[] = self::GetPixelData($image, $col);
        }
        return $data;
    }

    /**
     * Read an individual attribute (or tile)
     */
    private static function GetPixelData($image, $col, $mask = false)
    {
        // starting values for x
        $startx = $col * 8;

        $coldata = [];

        // rows
        for($line=0;$line<self::$height;$line++) {

            $linedata = [];

            // cols
            for($x=$startx;$x<$startx+8;$x++) {

                $rgb = imagecolorat($image, $x, $line);
                
                // get rgb values
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // pure black counts as ink
                if( $r == 0 && $g == 0 && $b == 0 ) {
                    $pixel = 0;
                }
                // anything else is paper
                else {
                    $pixel = 1;
                }

                // flip for mask
                if( $mask === true ) {
                    $pixel = !$pixel;
                }
                
                // add pixel value to this row
                $linedata[] = $pixel;
            }
            
            // add row of data
            $coldata[] = $linedata;
        }

        return $coldata;
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
     * Return sprite graphics in C format
     */
    public static function GetC()
    {
        $str = '';
        
        $numBytes = ((sizeof(self::$spriteData)*8*2)+(2*self::$height));

        $str .= 'const unsigned char '.SpecTiledTool::GetPrefix().'Sprite['.$numBytes.'] = {'.CR;
        
        // output front padding
        for($i=0;$i<self::$height;$i++) {
            if( $i > 0 ) {
                $str .= ', ';
            } 
            $str .= '0x00';
        }

        // get image and mask data
        for($i=0;$i<sizeof(self::$spriteData);$i++) {
            for($n=0;$n<8;$n++) {

                // mask data
                if( isset(self::$maskData[$i][$n])) {
                    $val = implode('', self::$maskData[$i][$n]);
                    $str .= ', 0x'.dechex(bindec($val));
                } else {
                    $str .= ', 0x0';
                }
                
                // sprite data
                $val = implode('', self::$spriteData[$i][$n]);
                $str .= ', 0x'.dechex(bindec($val));
            }
        }
        
        // output end padding
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
        $str = 'Error: BASIC sprite export is not implemented yet.';

        return $str;
    }

    /**
     * Return sprite graphics in assembly format
     */
    public static function GetAsm()
    {
        $str = '';

        $str .= 'SECTION rodata_user'.CR.CR;

        $baseName = SpecTiledTool::GetPrefix().'_sprite';

        if(self::$numColumns > 1) {
            for($i=1;$i<=self::$numColumns;$i++) {
                $str .= 'PUBLIC _'.$baseName.$i.CR;
            }
        }
        else {
            $str .= 'PUBLIC _'.$baseName.CR;
        }
        $str .= CR;

        // front padding
        for($line=0;$line<7;$line++) {
            $str .= 'defb @11111111, @00000000'.CR;
        }

        for($col=0;$col<self::$numColumns;$col++) {

            if( self::$numColumns > 1 ) {
                $str .= CR.'._'.$baseName.($col+1).CR;
            } else {
                $str .= CR.'._'.$baseName.CR;
            }
            
            // loop through data
            for($line=0;$line<sizeof(self::$spriteData[$col]);$line++) {

                // mask
                if( isset(self::$maskData[$col][$line])) {
                    $val = implode('', self::$maskData[$col][$line]);
                    $str .= 'defb @'.$val;
                } else {
                    $str .= 'defb @00000000';
                }
                
                // sprite
                $val = implode('', self::$spriteData[$col][$line]);
                $str .= ', @'.$val;

                $str .= CR;
            }

            $str .= CR;
            for($line=0;$line<8;$line++) {
                $str .= 'defb @11111111, @00000000'.CR;
            }
        }

        return $str;
    }
}
