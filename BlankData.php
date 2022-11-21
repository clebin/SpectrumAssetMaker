<?php

namespace ClebinGames\SpecTiledTool;

class BlankData
{
    private static $data = [];
    private static $codeName = 'data';
    private static $filename = false;

    public static function GetName()
    {
        return self::$name;
    }

    public static function SetName($name)
    {
        self::$name = SpecTiledTool::GetConvertedCodeName($name);
        self::$filename = SpecTiledTool::GetConvertedFilename($name);
    }

    public static function GetOutputFilename()
    {
        return self::$filename . '.' . SpecTiledTool::GetOutputFileExtension();
    }

    public static function GetOutputFilepath()
    {
        return SpecTiledTool::GetOutputFolder() . self::GetOutputFilename();
    }

    /**
     * 
     * Get code for screen in currently set language
     */
    public static function GetCode()
    {
        // add array length at the beginning
        array_unshift(self::$data, sizeof(self::$data));

        $str = 'SECTION ' . SpecTiledTool::GetCodeSection() . CR;

        $str .= SpecTiledTool::GetAsmArray(
            self::$codeName,
            self::$data,
            10,
            8
        ) . CR;

        return $str;
    }

    public static function Process($size)
    {
        self::$data = array_fill(0, $size, 0);
        self::$codeName = SpecTiledTool::GetConvertedCodeName(SpecTiledTool::GetName());

        file_put_contents(SpecTiledTool::GetOutputFilename(), self::GetCode());
    }
}
