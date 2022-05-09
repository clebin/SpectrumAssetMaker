<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a screen
 */
class Screen {

    public $num = 0;
    public $name = false;
    public $filename = false;
    public $data = [];

    public function __construct($num)
    {
        $this->SetNum($num);
    }

    public function SetData($data)
    {
        $this->data = $data;
    }

    public function SetNum($num = false)
    {
        $this->num = $num;
        $this->name = Tilemaps::$baseName.($num !== false ? $num : '');
        $this->filename = Tilemaps::$baseFilename.($num !== false ? '-'.$num : '');
    }

    public function SetName($name)
    {
        $this->name = SpecTiledTool::GetConvertedCodeName($name.'-tilemap');
        $this->filename = SpecTiledTool::GetConvertedFilename($name.'-tilemap');
    }

    public function GetData()
    {
        return $this->data;
    }

    public function GetName()
    {
        return $this->name;
    }

    public function GetOutputFilename()
    {
        return $this->filename.'.'.SpecTiledTool::GetOutputFileExtension();
    }

    public function GetOutputFilepath()
    {
        return SpecTiledTool::GetOutputFolder().$this->GetOutputFilename();
    }

    /**
     * 
     * Get code for screen in currently set language
     */
    public function GetCode()
    {
        switch( SpecTiledTool::GetFormat() ) {
            case 'c':
                return $this->GetC();
                break;
            default:
                return $this->GetAsm();
            break;
        }
    }

    /**
     * Get array of tile numbers for specified screen
     */
    public function GetTileNums() {

        $tileNums = [];
        foreach($this->data as $attr) {
            $tileNums[] = $attr->tileNum;
        }
        return $tileNums;
    }

    /**
     * Get screen represented in C
     */
    public function GetC()
    {
        $str = '';

        // add to first screen
        if( $this->num == 0 && Tilemaps::GetNumScreens() > 1) {
            $str .= '#define '.Tilemaps::$defineName.' '.Tilemaps::GetNumScreens().CR.CR;
        }

        // compression
        if(SpecTiledTool::$compression === 'rle' ) {
            
            $data = SpecTiledTool::CompressArrayRLE(
                $this->name, 
                $this->data, 
                false, 
            );
        } else {
            $data = $this->data;
        }
        
        // tile numbers
        $str .= SpecTiledTool::GetCArray(
            $this->name, 
            $data, 
            10
        ).CR;

        // // enemies
        // if( Tilemaps::$save_enemies === true && isset(self::$screens_enemies[$screen['num']]) ) {
        //     $str .= self::GetObjectsC('Enemies', self::$screens_enemies[$screen['num']]);
        // }

        // // objects
        // if( Tilemaps::$save_objects === true && isset(self::$screens_objects[$screen['num']]) ) {
        //     $str .= self::GetObjectsC('GameObjects', self::$screens_objects[$screen['num']]);
        // }

        // // colours
        // if( self::$save_colours === true && isset(self::$screens_colours[$screen['num']]) ) {
        //     $str .= self::GetObjectsC('Colours', self::$screens_colours[$screen['num']]);
        // }
        
        // last screen - set up an array of pointers to the screens
        if( Tilemaps::GetNumScreens() > 1 && 
            $this->num == Tilemaps::GetNumScreens()-1 ) {

            $str .= TileMaps::GetScreenArrayPointersC(Tilemaps::$baseName);
        }
        
        return $str;
    }

    /**
     * Get assembly code for this tilemap
     */
    public function GetAsm()
    {
        $str = 'SECTION '.SpecTiledTool::GetCodeSection().CR;
        
        if(SpecTiledTool::$compression === 'rle' ) {
            $data = SpecTiledTool::CompressArrayRLE(
                $this->name,
                $this->data, 
                true
            );
        } else {
            $data = $this->GetData();
        }

        $str .= SpecTiledTool::GetAsmArray(
            $this->name, 
            $data, 
            10, 
            8
        ).CR;

        return $str;
    }

}
