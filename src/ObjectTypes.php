<?php

namespace ClebinGames\SpectrumAssetMaker;

/**
 * Class containing functions to process and store object types
 */
class ObjectTypes
{
    private static $objectMapping = [];

    public static function ProcessFile($mapFilename)
    {
        // object map
        self::ReadMapFile($mapFilename);
    }

    /**
     * Read the object-types XML file.
     */
    public static function ReadMapFile($filename)
    {
        if (!file_exists($filename)) {
            echo 'Error: Object types file ' . $filename . ' not found' . CR;
            return false;
        }

        // object types
        $xml = file_get_contents($filename);
        $data = simplexml_load_string($xml);

        $success = self::ProcessData($data);

        return $success;
    }

    public static function ProcessData($data)
    {
        $index = 0;

        // print_r($data);
        foreach ($data->objecttype as $type) {

            $name = strval($type->attributes()->name);

            foreach ($type->property->attributes() as $key => $val) {

                if ($key == 'default') {
                    $index = intval($val);
                }
            }

            self::$objectMapping[$name] = $index;
        }

        echo 'Read ' . sizeof(self::$objectMapping) . ' object types.' . CR;
    }

    public static function GetIndex($name)
    {
        if (!isset(self::$objectMapping[$name])) {
            echo 'Object type mapping ' . $name . ' not set.' . CR;
            return false;
        }
        return self::$objectMapping[$name];
    }
}
