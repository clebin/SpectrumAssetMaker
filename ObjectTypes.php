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
            return false;
        }

        if( SpecTiledTool::GetPrefix() !== false ) {

            // set name for #define screens length
            self::$defineName = strtoupper(SpecTiledTool::GetPrefix()).'_'.self::$defineName;

            // set base name for code
            self::$baseName = SpecTiledTool::GetConvertedCodeName(SpecTiledTool::GetPrefix().'-objects');
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
            
            $name = $type->attributes()->name;

            foreach($type->property->attributes() as $key => $val) {
                
                if( $key == 'default') {
                    $index = $val;
                }
            }

            self::$objectMapping[$name] = $index;
        }
    }

    public static function GetObjectIndex($name)
    {
        return self::$objectMapping[$name];
    }
}