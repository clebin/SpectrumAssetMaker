<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tilemap with functions for reading and exporting
 */
class Tilemaps {
    
    // data arrays
    public static $screens = [];

    // object layers
    public static $screens_objects = [];
    public static $screens_enemies = [];
    public static $screen_colours = [];

    public static $save_objects = false;
    public static $save_enemies = false;
    public static $save_colours = false;

    public static $defineName = 'SCREENS_LEN';
    public static $baseName = 'tilemap';

    private static $screenNames = [];

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
            self::$defineName = strtoupper(SpecTiledTool::GetPrefix()).'_'.self::$defineName;
            self::$baseName = SpecTiledTool::GetPrefix().ucfirst(self::$baseName);
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
        $count = 0;

        foreach($data['layers'] as $layer) {

            $screen = new Screen($count);
            
            // read the Tiled data
            $data = self::ReadTilemapLayer($layer);

            // set data
            $screen->SetData($data);
            
            // set name
            if( SpecTiledTool::UseLayerNames() === true ) {
                $screen->SetName($layer['name']);
            }

            // add to arrays
            self::$screenNames[] = $screen->GetName();
            self::$screens[] = $screen;

            $count++;
        }

        // only one tilemap
        if( $count == 1 ) {
            self::$screens[0]->SetNum(false);
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

            $screen = new Screen($count);

            // set name?
            if( SpecTiledTool::UseLayerNames() === true ) {
                $screen->SetName($group['name']);
            }

            foreach($group['layers'] as $layer) {

                // get tilemap and object layers
                switch( strtolower($layer['name']) ) {

                    case 'tilemap':
                        $screen->SetData(self::ReadTilemapLayer($layer));
                    break;
                    
                    // case 'enemies':
                    //     if( $layer['visible'] == true ) {
                    //         self::$screens_enemies[$count] = self::ReadObjectLayer($layer);
                    //         self::$save_enemies = true;
                    //     }
                    // break;

                    // case 'objects':
                    //     if( $layer['visible'] == true ) {
                    //         self::$screens_objects[$count] = self::ReadObjectLayer($layer);
                    //         self::$save_objects = true;
                    //     }
                    // break;
                    
                    // case 'colours':
                    //     if( $layer['visible'] == true ) {
                    //     self::$screens_colours[$count] = self::ReadObjectLayer($layer);
                    //     self::$save_colours = true;
                    //     }
                    // break;
                }
            }
            self::$screenNames[] = $screen->GetName();
            self::$screens[] = $screen;

            $count++;
        }

        // only one tilemap
        if( $count == 1 ) {
            self::$screens[0]->SetNum(false);
        }
    }

    /**
     * Read a Tiled tilemap layer
     */
    public static function ReadTilemapLayer($layer)
    {
        $data = [];

        echo 'Reading tilemap.'.CR;
        foreach($layer['data'] as $tileNum) {

            $tileNum = intval($tileNum)-1;

            if( Tileset::TilesetIsSet() === true && Tileset::TileExists($tileNum) !== true ) {
                echo 'Warning: tile '.$tileNum.' not found. '.CR;
            }
            $data[] = $tileNum;
        }
        
        // check if screeName property is set
        $name = false;

        if( SpecTiledTool::$useLayerNames === true && isset($layer['properties'])) {
            foreach( $layer['properties'] as $prop ) {
                if( $prop['name'] == 'screenName' && strlen($prop['value']) > 0 ) {
                    $name = SpecTiledTool::GetConvertedCodeName($prop['value']);        
                }
            }
        }

        // return a Screen object
        return $data;
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
     * Get binaries.lst file with list of screen files
     */
    public static function GetBinariesLst()
    {
        $str = '';
        foreach(self::$screens as $screen) {
            $str .= $screen->GetCodeName().CR;
        }
        return $str;
    }

    /**
     * Get arrays of pointers to tilemaps, enemies, objects and colours
     */
    public static function GetScreenArrayPointersC($baseName)
    {
        $str = SpecTiledTool::GetPointerArrayC($baseName.'s', $baseName, sizeof(self::$screens));

        // pointers to enemies
        if( self::$save_enemies === true ) {
            $str .= SpecTiledTool::GetPointerArrayC($baseName.'sEnemies', $baseName.'Enemies', sizeof(self::$screens));
        }

        // pointers to objects 
        if( self::$save_objects === true ) {
            $str .= SpecTiledTool::GetPointerArrayC($baseName.'sObjects', $baseName.'Objects', sizeof(self::$screens));
        }

        // pointers to custom colours
        if( self::$save_colours === true ) {
            $str .= SpecTiledTool::GetPointerArrayC($baseName.'sColours', $baseName.'Colours', sizeof(self::$screens));
        }

        return $str;
    }

    public static function GetBaseName()
    {
        return self::$baseName;
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
}