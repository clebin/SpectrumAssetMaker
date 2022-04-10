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

                    $screen[] = $tileNum;
                    
                } else {

                    $screen[] = 0;
                    echo 'Warning: '.$tileNum.' not found. '.CR;
                }
            }
            
            // add to screens
            self::$screens[] = $screen;
        }
        return true;
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
        }
        
        // tile numbers
        $str .= SpecTiledTool::GetCArray(
            $baseName.'Tiles'.$screenNum, 
            self::$screens[$screenNum], 
            10
        ).CR;

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
            self::$screens[$screenNum], 
            10, 
            8
        ).CR;

        return $str;
    }
}