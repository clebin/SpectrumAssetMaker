<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tilemap with functions for reading and exporting
 */
class Tilemap {

    // data arrays
    private static $screens = [];

    /**
     * Read the tilemap JSON file.
     */
    public static function ReadFile($filename) {

        if(!file_exists($filename)) {

            SpecTiledTool::AddError('Map file not found');
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
                        Tileset::GetFlash($tileNum), 
                        Tileset::GetBright($tileNum), 
                        Tileset::GetPaper($tileNum), 
                        Tileset::GetInk($tileNum), 
                        Tileset::GetSolid($tileNum), 
                        Tileset::GetLethal($tileNum), 
                        Tileset::GetPlatform($tileNum), 
                        Tileset::GetCustom($tileNum)
                    );
                    
                } else {

                    $screen[] = new Attribute();
                    echo 'Warning: '.$tileNum.' not found. '.CR;
                }
            }
            
            // add to screens
            self::$screens[] = $screen;
        }
    }
    
    /**
     * Return the number of screens
     */
    public static function GetNumScreens()
    {
        return sizeof(self::$screens);
    }
    
    /**
     * Get code for all screens in currently set language
     */
    public static function GetCode()
    {
        $str = '';
        $screenNum = 0;

        for($i=0;$i<sizeof(self::$screens);$i++) {

            switch( SpecTiledTool::GetFormat() ) {
                case 'basic':
                    $str .= self::GetScreenBasic($i);
                    break;
                case 'c':
                    $str .= self::GetScreenC($i);
                    break;
                default:
                    $str .= self::GetScreenAsm($i);
                break;
            }
        }
        return $str;
    }

    /**
     * Get code for specified screen in current set language
     */
    public static function GetScreenCode($screenNum)
    {
        switch( SpecTiledTool::GetFormat() ) {
            case 'basic':
                return self::GetScreenBasic($screenNum);
                break;
            case 'c':
                return self::GetScreenC($screenNum);
                break;
            default:
                return self::GetScreenAsm($screenNum);
            break;
        }
    }

    /**
     * Get array of tile numbers for specified screen
     */
    public static function GetTileNumsFromScreen($num) {

        $screen = self::$screens[$num];
        $tileNums = [];
        foreach($screen as $attr) {
            $tileNums[] = $attr->tileNum;
        }
        return $tileNums;
    }

    /**
     * Get array of bytes (flash, bright, paper, ink) for specified screen
     */
    public static function GetBytesFromScreen($num) {

        $screen = self::$screens[$num];
        $screenBytes = [];
        foreach($screen as $attr) {
            $screenBytes[] = self::GetAttrColours($attr);
        }
        return $screenBytes;
    }

    /**
     * Get array of bytes (flash, bright, paper, ink) for specified screen
     */
    public static function GetGamePropertiesFromScreen($num) {

        $screen = self::$screens[$num];

        $screenGameProperties = [];
        
        $count = 0;
        $val = '';
        foreach($screen as $attr) {

            // even numbers - first half of byte (high)
            if( $count % 2 == 0 ) {
                $val = self::GetAttrGameProperties($attr);
            }
            // odd numbers - second half of byte (low)
            else {
                $val .= self::GetAttrGameProperties($attr);
                $screenGameProperties[] = $val; 
                $val = '';
            }
            $count++;
        }
        return $screenGameProperties;
    }
    
    /**
     * Get screen represented in C
     */
    public static function GetScreenC($screenNum)
    {
        $str = '';

        if( SpecTiledTool::GetPrefix() !== false ) {
            $baseName = SpecTiledTool::GetPrefix().'Screen';
        } else {
            $baseName = 'screen';
        }

        // add to first screen
        if( $screenNum == 0 ) {
            $str .= '#define '.strtoupper(SpecTiledTool::GetPrefix()).'_SCREENS_LEN '.sizeof(self::$screens).CR.CR;
        
            // typedef for screen structure
            $str .= 'typedef struct screen {'.CR.
                '   unsigned char *tiles;'.CR.
                '   unsigned char *colours;'.CR.
                '   unsigned char *properties;'.CR.
                '   int exits[4];'.CR.
                '   char *name;'.CR.
            '};'.CR.CR;
        }
        
        // tile numbers
        $str .= SpecTiledTool::GetCArray(
            $baseName.'Tiles'.$screenNum, 
            self::GetTileNumsFromScreen($screenNum), 
            10
        ).CR;

        // attribute colours
        $str .= SpecTiledTool::GetCArray(
            $baseName.'Colours'.$screenNum, 
            self::GetBytesFromScreen($screenNum), 
            2
        ).CR;

        // game properties 
        if( SpecTiledTool::$saveGameProperties === true ) {

            $str .= SpecTiledTool::GetCArray(
                $baseName.'Properties'.$screenNum, 
                self::GetGamePropertiesFromScreen($screenNum), 
                2
            ).CR;
        }

        // last screen - set up an array of pointers to the screens
        if( $screenNum == sizeof(self::$screens)-1 ) {
            
            $str .= '// array of pointers to all screens'.CR;

            // tile number arrays
            $str .= 'const unsigned char *'.$baseName.'sTiles['.sizeof(self::$screens).'] = {';
            for($i=0;$i<sizeof(self::$screens);$i++) {
                if($i>0) {
                    $str .= ', ';
                }
                $str .= $baseName.'Tiles'.$i;
            }
            $str .= '};'.CR;

            // attribute arrays
            $str .= 'const unsigned char *'.$baseName.'sColours['.sizeof(self::$screens).'] = {';
            for($i=0;$i<sizeof(self::$screens);$i++) {
                if($i>0) {
                    $str .= ', ';
                }
                $str .= $baseName.'Colours'.$i;
            }
            $str .= '};'.CR;

            // game properties arrays
            if( SpecTiledTool::$saveGameProperties === true ) {

                $str .= 'const unsigned char *'.$baseName.'sProperties['.sizeof(self::$screens).'] = {';
                for($i=0;$i<sizeof(self::$screens);$i++) {
                    if($i>0) {
                        $str .= ', ';
                    }
                    $str .= $baseName.'Properties'.$i;
                }
                $str .= '};'.CR;
            }
        }
        
        return $str;
    }

    /**
     * Get screen represented in BASIC
     */
    public static function GetScreenBasic($screenNum)
    {
        $str = SpecTiledTool::GetBasicArray(
            SpecTiledTool::GetPrefix().'ScreenTiles'.$screenNum, 
            self::GetTileNumsFromScreen($screenNum), 
            10
        ).CR;

        $str .= SpecTiledTool::GetBasicArray(
            SpecTiledTool::GetPrefix().'ScreenColours'.$screenNum, 
            self::GetBytesFromScreen($screenNum), 
            2
        ).CR;
        
        // game properties 
        if( SpecTiledTool::$saveGameProperties === true ) {

            $str .= SpecTiledTool::GetBasicArray(
                SpecTiledTool::GetPrefix().'ScreenProperties'.$screenNum, 
                self::GetGamePropertiesFromScreen($screenNum), 
                2
            ).CR;
        }
        
        return $str;
    }

    /**
     * Get assembly code for this tilemap
     */
    public static function GetScreenAsm($screenNum)
    {
        $str = SpecTiledTool::GetAsmArray(
            SpecTiledTool::GetPrefix().'_screen_'.$screenNum.'_attribute_tiles', 
            self::GetTileNumsFromScreen($screenNum), 
            10, 
            8
        ).CR;

        $str .= SpecTiledTool::GetAsmArray(
            SpecTiledTool::GetPrefix().'_screen_'.$screenNum.'_attribute_colours', 
            self::GetBytesFromScreen($screenNum), 
            2, 
            8
        ).CR;

        // game properties 
        if( SpecTiledTool::$saveGameProperties === true ) {

            $str .= SpecTiledTool::GetAsmArray(
                SpecTiledTool::GetPrefix().'_screen_'.$screenNum.'_attribute_properties', 
                self::GetGamePropertiesFromScreen($screenNum), 
                2, 
                8
            ).CR;
        }
        
        return $str;
    }

    /**
     * Get byte containing flash, bright, paper and ink
     * from attribute
     */
    public static function GetAttrColours($attr)
    {
        return 
        ( $attr->flash == true ? '1' : '0'). // flash
        ( $attr->bright == true ? '1' : '0'). // bright
        str_pad(decbin($attr->paper), 3, '0', STR_PAD_LEFT ).
        str_pad(decbin($attr->ink), 3, '0', STR_PAD_LEFT );
    }

    /**
     * Get byte containing flash, bright, paper and ink
     * from attribute
     */
    public static function GetAttrGameProperties($attr)
    {
        return 
        ( $attr->solid == true ? '1' : '0').
        ( $attr->lethal == true ? '1' : '0').
        ( $attr->platform == true ? '1' : '0').
        ( $attr->custom == true ? '1' : '0');
    }
}