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
            $screen = [];

            foreach($layer['data'] as $tileNum) {

                $tileNum = intval($tileNum);

                if( Tileset::TileExists($tileNum) === true ) {

                    $screen[] = [
                        'tile' => $tileNum,
                        'paper' => Tileset::GetPaper($tileNum), 
                        'ink' => Tileset::GetInk($tileNum), 
                        'bright' => Tileset::GetBright($tileNum), 
                        'flash' => false
                    ];
                    

                } else {

                    $screen[] = [
                        'tile' => 0,
                        'paper' => 0, 
                        'ink' => 7, 
                        'bright' => false, 
                        'flash' => false
                    ];

                    echo 'Warning: '.$tileNum.' not found. '.CR;
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
     * Get the assembly code for this tilemap
     */
    public static function GetAsm()
    {
        $str = '';

        $screenNum = 0;
        foreach(self::$screens as $screen) {

            // output tile numbers
            $str .= '._'.SpecScreenTool::$prefix.'_screen_'.$screenNum.'_attribute_tiles'.CR;

            $count = 0;
            foreach($screen as $attr) {

                if( $count > 0 ) {
                    if( $count % 4 == 0 ) {
                        $str .= CR;
                    } else {
                        $str .= ', ';
                    }
                }
                $str .= 'defb @'.self::GetBinaryVal($attr['tile'], 8);
                $count++;
            }

            $str .= CR.CR;

            // output paper/ink/bright/flash
            $str .= '._'.SpecScreenTool::$prefix.'_screen_'.$screenNum.'_attribute_values'.CR;

            $count = 0;
            foreach($screen as $attr) {

                if( $count > 0 ) {
                    if( $count % 4 == 0 ) {
                        $str .= CR;
                    } else {
                        $str .= ', ';
                    }
                }

                $str .= 'defb @';
                $str .= self::GetScreenByte($attr);

                $count++;
            }

            $screenNum++;
        }
        
        return $str;
    }

    public static function GetScreenByte($attr)
    {
        return '0'. // flash
        ( $attr['bright'] == true ? '1' : '0'). // bright
        self::GetBinaryVal($attr['paper'], 3). // paper
        self::GetBinaryVal($attr['ink'], 3); // ink
    }
}