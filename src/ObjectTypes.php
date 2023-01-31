<?php

namespace ClebinGames\SpectrumAssetMaker;

/**
 * Class containing functions to process and store object types
 */
class ObjectTypes
{
    private static $objectMapping = [];
    public static $isValid = false;

    public static function ProcessFile($mapFilename)
    {
        // object map
        self::ReadFile($mapFilename);
    }

    /**
     * Read the object-types XML file.
     */
    public static function ReadFile($filename)
    {
        if (!file_exists($filename)) {
            echo 'Error: Object types file ' . $filename . ' not found' . CR;
            return false;
        }

        // object types
        $xml = file_get_contents($filename);
        $data = simplexml_load_string($xml);

        self::$isValid = self::ProcessData($data);
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

            if (App::GetVerbosity() == App::VERBOSITY_VERBOSE) {
                echo 'Object type: ' . $name . ' (index ' . $index . ')' . CR;
            }

            self::$objectMapping[$name] = $index;
        }

        if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
            echo 'Objects:  Processed ' . sizeof(self::$objectMapping) . ' object types.' . CR;
        }
    }

    public static function GetIndex($name)
    {
        // print_r(self::$objectMapping);
        if ($name != '' && isset(self::$objectMapping[$name])) {
            return self::$objectMapping[$name];
        }
    }
}
