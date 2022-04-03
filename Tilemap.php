<?php
namespace ClebinGames\SpecScreenTool;

class Tilemap {

    // which map layer to start on? (eg. to use first map layer as a background colour)
    public static $startLayer = 0;

    // data arrays
    private static $screens = [];

    /**
     * Read the tilemap JSON file.
     */
    public static function ReadFile($filename) {

        if(!file_exists($filename)) {

            SpecScreenTool::AddError('Map file not found');
            return false;
        }

        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        // each layer counts as one screen
        foreach($data['layers'] as $layer) {

            // now do paper, ink, etc
            $screen = [];

            foreach($layer['data'] as $tileNum) {

                $tileNum = intval($tileNum)-1;

                if( Tileset::TileExists($tileNum) === true ) {

                    $screen[] = new Attribute(
                        $tileNum, 
                        false, 
                        Tileset::GetBright($tileNum), 
                        Tileset::GetPaper($tileNum), 
                        Tileset::GetInk($tileNum), 
                    );
                    
                } else {

                    $screen[] = new Attribute();
                    // echo 'Warning: '.$tileNum.' not found. '.CR;
                }
            }
            
            // add to screens
            self::$screens[] = $screen;

        }
    }
    
    /**
     * Return a binary string of a set length from a number
     */
    public static function GetBinaryVal($num, $length) {

        return str_pad( decbin($num), $length, '0', STR_PAD_LEFT );
    }

    /**
     * Get C code for this tilemap
     * 
     * @todo Implement C support
     */
    public static function GetC()
    {
        
    }

    public static function GetNumScreens()
    {
        return sizeof(self::$screens);
    }
    
    public static function GetCode()
    {
        switch( SpecScreenTool::GetFormat() ) {
            case 'basic':
                return self::GetBasic();
            default:
                return self::GetAsm();
            break;
        }
    }

    public static function GetScreenCode($screenNum)
    {
        switch( SpecScreenTool::GetFormat() ) {
            case 'basic':
                return self::GetScreenBasic($screenNum);
            default:
                return self::GetScreenAsm($screenNum);
            break;
        }
    }

    /**
     * Get BASIC code for thsi tilemap
     * 
     * @todo Implement BASIC support
     */
    public static function GetBasic()
    {
        $str = '';
        $screenNum = 0;
        for($i=0;$i<sizeof(self::$screens);$i++) {
            $str .= self::GetScreenBasic($i);
        }
        return $str;
    }


    public static function GetScreenBasic($screenNum)
    {
        $screen = self::$screens[$screenNum];

        // tile numbers
        $str = 'Dim '.SpecScreenTool::GetPrefix().'ScreenTiles'.$screenNum.'(767) as uByte => { _'.CR;

        $count = 0;
        foreach($screen as $attr) {

            if( $count > 0 ) {
                $str .= ',';
                if( $count % 32 == 0 ) {
                    $str .= ' _'.CR;
                }
            }
            $str .= $attr->tileNum;
            $count++;
        }
        $str .= ' _'.CR.'}'.CR.CR;
        
        // attribute values
        $str .= 'Dim '.SpecScreenTool::GetPrefix().'ScreenValues'.$screenNum.'(767) => { _'.CR;
        
        $count = 0;
        foreach($screen as $attr) {
            if( $count > 0 ) {
                $str .= ',';
                if( $count % 32 == 0 ) {
                    $str .= ' _'.CR;
                }
            }
            $str .= bindec(self::GetScreenByte($attr));
            $count++;
        }

        $str .= ' _'.CR.'}'.CR;

        return $str;
    }

    /**
     * Get assembly for all screens
     */
    public static function GetAsm()
    {
        $str = '';

        $screenNum = 0;
        for($i=0;$i<sizeof(self::$screens);$i++) {
            $str .= self::GetScreenAsm($i);
        }
        return $str;
    }

    /**
     * Get assembly code for this tilemap
     */
    public static function GetScreenAsm($screenNum)
    {
        $str = '';

        $screen = self::$screens[$screenNum];

        // output tile numbers
        $str .= '._'.SpecScreenTool::GetPrefix().'_screen_'.$screenNum.'_attribute_tiles'.CR;

        $count = 0;
        foreach($screen as $attr) {

            if( $count % 4 == 0 ) {
                $str .= CR.'defb ';
            } else {
                $str .= ', ';
            }
        
            $str .= '@'.self::GetBinaryVal($attr->tileNum, 8);
            $count++;
        }

        $str .= CR.CR;

        // output paper/ink/bright/flash
        $str .= '._'.SpecScreenTool::GetPrefix().'_screen_'.$screenNum.'_attribute_values'.CR;

        $count = 0;
        foreach($screen as $attr) {

            if( $count % 4 == 0 ) {
                $str .= CR.'defb ';
            } else {
                $str .= ', ';
            }

            $str .= '@';
            $str .= self::GetScreenByte($attr);

            $count++;
        }

        return $str;
    }

    public static function GetScreenByte($attr)
    {
        return 
        ( $attr->flash == true ? '1' : '0'). // flash
        ( $attr->bright == true ? '1' : '0'). // bright
        self::GetBinaryVal($attr->paper, 3). // paper
        self::GetBinaryVal($attr->ink, 3); // ink
    }
}