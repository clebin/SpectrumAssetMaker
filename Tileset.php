<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tileset with functions for reading and exporting
 */
class Tileset
{
    private static $tilesetIsSet = false;

    // static array of tiles
    private static $tiles = [];
    public static $large_tileset = false;
    public static $codeName = 'tileset';
    public static $defineName = 'tileset-len';
    
    /**
     * Read the tileset JSON file
     */
    public static function ReadFile($filename)
    {
        if( SpecTiledTool::GetName() !== false ) {
            self::$codeName = SpecTiledTool::GetName().'-'.self::$codeName;
            self::$defineName = SpecTiledTool::GetName().'-'.self::$defineName;
        }
        
        self::$codeName = SpecTiledTool::GetConvertedCodeName(self::$codeName);
        self::$defineName = SpecTiledTool::GetConvertedConstantName(self::$defineName);


        if(!file_exists($filename)) {
            return false;
        }
        
        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        $count = 0;
        foreach($data['tiles'] as $tile) {

            $id = intval($tile['id']);

            // save to tiles array using id as key
            self::$tiles[] = new Tile($id, $tile['properties']);

            $count++;
        }

        // need to represent tile numbers with 16 bits
        if( sizeof(self::$tiles) > 256 ) {
            self::$large_tileset = true;
        }

        echo 'Tileset: added '.$count.' tiles.'.CR;

        self::$tilesetIsSet = true;
        
        return true;
    }

    public static function TilesetIsSet()
    {
        return self::$tilesetIsSet;
    }
    
    /**
     * Get number of tiles in tileset
     */
    public static function GetNumTiles()
    {
        return sizeof(self::$tiles);
    }

    /**
     * Return whether a tile with a particular id exists in the tileset
     */
    public static function TileExists($id){
        return isset(self::$tiles[$id]);
    }

    public static function GetBinariesLst()
    {
        return self::GetOutputBaseFilename();
    }

    /**
     * Get tile graphics code in currently set format/language
     */
    public static function GetCode()
    {
        switch( SpecTiledTool::GetFormat() ) {
            
            case 'c':
                return self::GetC();
                break;

            default:
                return self::GetAsm();
                break;
        }
    }

    /**
     * Return tileset in assembly format
     */
    public static function GetAsm()
    {
        $str = 'SECTION '.SpecTiledTool::GetCodeSection().CR;
        
        $str .= CR;
        // tile info
        $colours = [];
        $properties = [];

        foreach(self::$tiles as $tile) {
            $colours[] = $tile->GetColoursByte();
            $properties[] = $tile->GetPropertiesByte();
        }
        
        // colours
        $str .= SpecTiledTool::GetAsmArray(
            self::$codeName.'Colours', 
            $colours, 
            2
        ).CR;

        // properties
        if( SpecTiledTool::ReplaceFlashWithSolid() === false ) {
            $str .= SpecTiledTool::GetAsmArray(
                self::$codeName.'Properties', 
                $properties, 
                2
            ).CR;
        }

        return $str;
    }

    /**
     * Return C array of tile colours and properties
     */
    public static function GetC()
    {
        $str = '';

        $str .= '#define '.strtoupper(self::$defineName).' '.sizeof(self::$tiles).CR.CR;

        // tile info
        $colours = [];
        $properties = [];
        foreach(self::$tiles as $tile) {
            $colours[] = $tile->GetColoursByte();
            $properties[] = $tile->GetPropertiesByte();
        }
        
        // colours
        $str .= SpecTiledTool::GetCArray(
            self::$codeName.'Colours', 
            $colours, 
            2
        ).CR;

        // properties array
        if( SpecTiledTool::ReplaceFlashWithSolid() === false ) {
            $str .= SpecTiledTool::GetCArray(
                self::$codeName.'Properties', 
                $properties, 
                2
            ).CR;
        }

        return $str;
    }

    public static function Process($filename)
    {
        $success = self::ReadFile($filename);

        if( $success === true ) {        
            file_put_contents(SpecTiledTool::GetOutputFilename('tileset'), self::GetCode());
        }
    }
}
