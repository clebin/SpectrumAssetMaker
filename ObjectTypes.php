<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class containing functions to process and store object types
 */
class ObjectTypes {

    private static $objectMapping = [];

    /**
     * Read the object-types XML file.
     */
    public static function ReadFile($filename) {

        if(!file_exists($filename)) {
            echo 'Error: Object types file '.$filename.' not found'.CR;
            return false;
        }

        $xml = file_get_contents($filename);
        
        $data = simplexml_load_string($xml);

        $success = self::ProcessData($data);

        return $success;
    }

    public static function ProcessData($data)
    {
        $index = 0;

        // print_r($data);
        foreach($data->objecttype as $type) {
            
            $name = strval($type->attributes()->name);

            foreach($type->property->attributes() as $key => $val) {
                
                if( $key == 'default') {
                    $index = intval($val);
                }
            }
            
            self::$objectMapping[$name] = $index;
        }

        echo 'Read '.sizeof(self::$objectMapping).' object types.'.CR;
    }

    public static function Process($filename)
    {
        self::ReadFile($filename);
    }

    public static function GetIndex($name)
    {
        if( !isset(self::$objectMapping[$name])) {
            echo 'Error: object type '.$name.' not found. Size of objectMapping is '.sizeof(self::$objectMapping).CR;
            exit();
        }
        return self::$objectMapping[$name];
    }
}