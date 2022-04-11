<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tilemap with functions for reading and exporting
 */
class Tilemap {

    // data arrays
    private static $screens = [];

    // object layers
    private static $screens_objects = [];
    private static $screens_enemies = [];
    private static $screen_colours = [];

    // allowed properties on enemies, objects, etc.
    private static $object_allowed_properties = [
        'collectable',
        'deltax', 
        'deltay', 
        'speed', 
        'numhits', 
        'transient', 
        'lethal', 
        'endval', 
        'movement'
    ];

    /**
     * Read the tilemap JSON file.
     */
    public static function ReadFile($filename) {

        if(!file_exists($filename)) {
            return false;
        }
        
        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        // read with object layers
        if( isset($data['layers']['layers']) ) {
            self::ReadFileWithObjects($data);
        }
        // read simple
        else {
            self::ReadFileSimple($data);
        }
    }

    public static function ReadFileSimple($data)
    {
        // each layer counts as one screen
        foreach($data['layers'] as $layer) {

            self::$screens[] = self::GetLayerTiles($layer);
        }
        return true;
    }

    public static function ReadFileWithObjects($data)
    {
        // loop through groups
        foreach($data['layers'] as $group) {
            
            foreach($group as $layer) {

                // get tilemap and object layers
                switch( strtolower($layer['name']) ) {
                    case 'tilemap':
                        self::$screens[] = self::ReadTilemapLayer($layer);
                    break;

                    case 'enemies':
                        self::$screens_objects[] = self::ReadObjectLayer($layer);
                    break;
                }
            }
        }
    }

    public static function ReadTilemapLayer($layer)
    {
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

        return $screen;
    }

    public static function ReadObjectLayer($data)
    {
        $objects = [];
        foreach($layer['objects'] as $json_object) {

            $obj = [];

            // type
            $obj['type'] = $json_object['type'];

            // custom properties
            foreach($json_object['properties'] as $prop) {
                $obj[$prop['name']] = $prop['value'];
            }

            // x and y positions
            $obj['x'] = intval($json_obj['x']);
            $obj['y'] = intval($json_obj['y']);

            // add to array
            $objects[] = $obj;
        }
        return $objects;
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
            $defineName = 'SCREENS_LEN';
            $baseName = SpecTiledTool::GetPrefix().'Screen';
        } else {
            $defineName = SpecTiledTool::GetPrefix().'_SCREENS_LEN';
            $baseName = 'screen';
        }

        // add to first screen
        if( $screenNum == 0 ) {
            $str .= '#define '.$defineName.' '.sizeof(self::$screens).CR.CR;
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