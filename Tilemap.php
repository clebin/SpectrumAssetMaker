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

    private static $save_objects = false;
    private static $save_enemies = false;
    private static $save_colours = false;

    private static $defineName = 'SCREENS_LEN';
    private static $baseName = 'screen';

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

        if( SpecTiledTool::GetPrefix() !== false ) {
            self::$defineName = SpecTiledTool::GetPrefix().'_'.self::$defineName;
            self::$baseName = SpecTiledTool::GetPrefix().self::$baseName;
        }

        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        // read simple
        if( isset($data['layers'][0]['data']) ) {
            self::ReadFileSimple($data);
        }
        // read with object layers
        else {
            self::ReadFileWithObjects($data);
        }
    }

    /**
     * Read a simple file with only tilemap layers and no groups
     */
    public static function ReadFileSimple($data)
    {
        // each layer counts as one screen
        foreach($data['layers'] as $layer) {

            self::$screens[] = self::ReadTilemapLayer($layer);
        }
        return true;
    }

    /**
     * Read a file with object layers and tilemap layers in groups
     * 
     * Correct layer names:
     * - tilemap
     * - enemies
     * - objects
     * - colours
     * 
     */
    public static function ReadFileWithObjects($data)
    {
        // loop through groups
        $count = 0;
        foreach($data['layers'] as $group) {
            
            foreach($group['layers'] as $layer) {

                // get tilemap and object layers
                switch( strtolower($layer['name']) ) {
                    case 'tilemap':
                        self::$screens[$count] = self::ReadTilemapLayer($layer);
                    break;

                    case 'enemies':
                        if( $layer['visible'] == true ) {
                            self::$screens_enemies[$count] = self::ReadObjectLayer($layer);
                            self::$save_enemies = true;
                        }
                    break;

                    case 'objects':
                        if( $layer['visible'] == true ) {
                            self::$screens_objects[$count] = self::ReadObjectLayer($layer);
                            self::$save_objects = true;
                        }
                    break;
                    
                    // case 'colours':
                    //     if( $layer['visible'] == true ) {
                    //     self::$screens_colours[$count] = self::ReadObjectLayer($layer);
                    //     self::$save_colours = true;
                    //     }
                    // break;
                }
            }
            $count++;
        }
    }

    /**
     * Read a Tiled tilemap layer
     */
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

    /**
     * Read an Tiled object layer (can be enemies, objects or colours)
     */
    public static function ReadObjectLayer($layer)
    {
        return;
        $objects = [];
        foreach($layer['objects'] as $json_object) {

            // create new object
            $obj = [
                'type' => $json_object['type']
            ];

            // name (optional)
            if( $json_object['name'] != '' ) {
                $obj['name'] = $json_object['name'];
            }

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
     * 
     */
    public static function GetStructsC()
    {
        $str = '';

        // enemies
        if( self::$save_enemies === true ) {
            $str .= '
typedef struct Enemy {
    char[8] type;
    char[8] movement;
    uint8_t x;
    uint8_t y;
    uint8_t lethal; // boolean
    uint8_t transient; // boolean
    uint8_t endval;
    uint8_t numhits;
    uint8_t speed;
};'.CR.CR;
        }

        // objects
        if( self::$save_objects === true ) {
            $str .= '
typedef struct GameObject {
    char[12] name;
    char[8] type;
    uint8_t row;
    uint8_t col;
    uint8_t lethal; // boolean
    uint8_t collectable; // boolean
    uint8_t numhits;
};'.CR.CR;
        }

        return $str;
    }

    /**
     * Get screen represented in C
     */
    public static function GetScreenC($screenNum)
    {
        $str = '';

        // add to first screen
        if( $screenNum == 0 ) {
            $str .= '#define '.self::$defineName.' '.sizeof(self::$screens).CR.CR;

            //$str .= self::GetStructsC();
        }
        
        // tile numbers
        $str .= SpecTiledTool::GetCArray(
            self::$baseName.'Tiles'.$screenNum, 
            self::$screens[$screenNum], 
            10
        ).CR;

        // enemies
        if( self::$save_enemies === true && isset(self::$screens_enemies[$screenNum]) ) {
            $str .= self::GetObjectsC('Enemies', self::$screens_enemies[$screenNum]);
        }

        // objects
        if( self::$save_objects === true && isset(self::$screens_objects[$screenNum]) ) {
            $str .= self::GetObjectsC('GameObjects', self::$screens_objects[$screenNum]);
        }

        // colours
        if( self::$save_colours === true && isset(self::$screens_colours[$screenNum]) ) {
            $str .= self::GetObjectsC('Colours', self::$screens_colours[$screenNum]);
        }
        
        // last screen - set up an array of pointers to the screens
        if( $screenNum == sizeof(self::$screens)-1 ) {

            $str .= self::GetScreenArrayPointersC(self::$baseName);
        }
        
        return $str;
    }

    /**
     * Get arrays of pointers to tilemaps, enemies, objects and colours
     */
    public static function GetScreenArrayPointersC($baseName)
    {
        $str = self::GetPointerArrayC($baseName.'sTiles', $baseName.'Tiles', sizeof(self::$screens));

        // pointers to enemies
        if( self::$save_enemies === true ) {
            $str .= self::GetPointerArrayC($baseName.'sEnemies', $baseName.'Enemies', sizeof(self::$screens));
        }

        // pointers to objects 
        if( self::$save_objects === true ) {
            $str .= self::GetPointerArrayC($baseName.'sObjects', $baseName.'Objects', sizeof(self::$screens));
        }

        // pointers to custom colours
        if( self::$save_colours === true ) {
            $str .= self::GetPointerArrayC($baseName.'sColours', $baseName.'Colours', sizeof(self::$screens));
        }

        return $str;
    }

    /**
     * Get C code for an array of pointers
     */
    public static function GetPointerArrayC($arrayName, $itemsBaseName, $size = 0)
    {
        $str = '';

        // tile number arrays
        $str .= 'const unsigned char *'.$arrayName.'['.$size.'] = {';
        
        for($i=0;$i<$size;$i++) {
            if($i>0) {
                $str .= ', ';
            }
            $str .= $itemsBaseName.$i;
        }
        $str .= '};'.CR;
    
        return $str;
    }

    /**
     * Get C code for eneemies
     */
    public static function GetEnemiesC()
    {
        // $name = $baseName.'Enemies'.$screenNum;
        
    }

    /**
     * Get C code for objects
     */
    public static function GetObjectsC()
    {
        // $name = $baseName.'Objects'.$screenNum;
    }

    /**
     * Get C codde for custom colours
     */
    public static function GetColoursC()
    {
        // $name = $baseName.'Colours'.$screenNum;
    }

    /**
     * Get assembly code for this tilemap
     */
    public static function GetScreenAsm($screenNum)
    {
        $name = self::$baseName.'Tiles'.$screenNum;

        $str = 'SECTION '.SpecTiledTool::$section.CR;
        
        $str .= SpecTiledTool::GetAsmArray(
            $name, 
            (SpecTiledTool::$compression === true ? SpecTiledTool::ConvertArrayToRLE(self::$screens[$screenNum], true, $name) : self::$screens[$screenNum]), 
            10, 
            8
        ).CR;

        return $str;
    }
}