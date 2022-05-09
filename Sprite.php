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

    public static $codeName = 'sprite';
    public static $section = 'rodata_user';

    /**
     * Read a black & white PNG or GIF file
     */
    public static function ReadFiles($spriteFile, $maskFile = false)
    {
        if( SpecTiledTool::GetPrefix() !== false ) {
            self::$codeName = SpecTiledTool::GetConvertedCodeName(SpecTiledTool::GetPrefix().'-sprite');
        }

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
            self::$maskData = self::GetImageData(self::$maskImage, true);
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

    public static function GetImageData($image, $mask = false)
    {
        $data = [];

        // loop through columns
        for($col=0;$col<self::$numColumns;$col++) {
            $data[] = self::GetPixelData($image, $col, $mask);
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
                    
                    $pixel = ($mask === true ? 1 : 0);
                }
                // anything else is paper
                else {
                    $pixel = ($mask === true ? 0 : 1);
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
        $str = 'Error: C sprite export is not supported.';

        return $str;
    }

    /**
     * Return sprite graphics in assembly format
     */
    public static function GetAsm()
    {
        $str = 'SECTION '.self::$section.CR.CR;

        $str .= 'PUBLIC _'.self::$codeName.CR.CR;

        // front padding
        for($line=0;$line<7;$line++) {
            if( self::$maskImage !== false ) {
                $str .= 'defb @11111111, @00000000'.CR;
            } else {
                $str .= 'defb @00000000'.CR;
            }
        }

        $str .= CR.'._'.self::$codeName.CR;
        
        for($col=0;$col<self::$numColumns;$col++) {

            // loop through data
            for($line=0;$line<sizeof(self::$spriteData[$col]);$line++) {

                // mask
                if( self::$maskImage !== false ) {
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
                // unmasked
                else {
                    $val = implode('', self::$spriteData[$col][$line]);
                    $str .= 'defb @'.$val.CR;
                }
            }

            $str .= CR;
            // footer padding
            for($line=0;$line<8;$line++) {
                if( self::$maskImage !== false ) {
                    $str .= 'defb @11111111, @00000000'.CR;
                } else {
                    $str .= 'defb @00000000'.CR;
                }
            }
        }

        return $str;
    }
}
