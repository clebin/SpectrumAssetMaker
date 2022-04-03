<?php
namespace ClebinGames\SpecScreenTool;

class Tilemap {

    public static $prefix = 'tile';

    // which map layer to start on? (eg. to use first map layer as a background colour)
    public static $startLayer = 0;

    // data arrays
    private static $screens = [];

    private static $attributeMaps = [];
    private static $paperMaps = [];
    private static $inkMaps = [];
    private static $brightMaps = [];

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
            $paperMap = [];
            $inkMap = [];
            $brightMap = [];

            foreach($layer['data'] as $tileNum) {

                $tileNum = intval($tileNum);

                if( Tileset::TileExists($tileNum) === true ) {
                    $paperMap[] = Tileset::GetPaper($tileNum);
                    $inkMap[] = Tileset::GetInk($tileNum);
                    $brightMap[] = (Tileset::GetBright($tileNum) == true ? 1 : 0);
                } else {
                    echo 'Warning: '.$tileNum.' not found. '.CR;
                }
            }

            // add to screens
            self::$screens[] = [
                'attributes' => $layer['data'], 
                'paper' => $paperMap,  
                'ink' => $inkMap, 
                'bright' => $brightMap
            ];
        }
    }
    
    /**
     * Get the assembly code for this tilemap
     */
    public static function GetAsm()
    {
        $str = '';

        $count = 0;
        foreach(self::$screens as $screen) {

            // output attribute numbers
            $str .= '._'.SpecScreenTool::$prefix.'_screen_'.$count.'_attr'.CR;
            $str .= implode(',', $screen['attributes']).CR.CR;

            // output paper
            $str .= '._'.SpecScreenTool::$prefix.'_screen_'.$count.'_paper'.CR;
            $str .= implode(',', $screen['paper']).CR.CR;

            // output ink
            $str .= '._'.SpecScreenTool::$prefix.'_screen_'.$count.'_ink'.CR;
            $str .= implode(',', $screen['ink']).CR.CR;

            // output bright
            $str .= '._'.SpecScreenTool::$prefix.'_screen_'.$count.'_bright'.CR;
            $str .= implode(',', $screen['bright']).CR.CR;

            // output solid map
            // $str .= '._'.SpecScreenTool::$prefix.'_screen_'.$count.'_solid'.CR;
            // $str .= implode(',', $screen['solid']);

            // output lethal map
            // $str .= '._'.SpecScreenTool::$prefix.'_screen_'.$count.'_lethal'.CR;
            // $str .= implode(',', $screen['lethal']);
            
            $count++;
        }

        return $str;
    }

}