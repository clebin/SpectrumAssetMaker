<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing a tilemap
 */
class Tilemap {

    public $num = 0;
    public $name = false;
    public $filename = false;
    public $data = [];
    public $width = false;
    public $height = false;

    public function __construct($num, $layer)
    {
        $this->num = $num;

        $this->data = $this->ReadLayer($layer);
    }

    public function SetData($data)
    {
        $this->data = $data;
    }

    public function SetDimensions($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
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

    /**
     * Read a Tiled tilemap layer
     */
    public function ReadLayer($layer)
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

        // dimensions
        $this->width = $layer['width'];
        $this->height = $layer['height'];

        // return a Screen object
        return $data;
    }

    public function GetOutputFilename()
    {
        $filename = SpecTiledTool::GetOutputFolder();
        $filename .= $this->filename.'.'.SpecTiledTool::GetOutputFileExtension();
        
        return $filename;
    }
    
    /**
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
     * Get data
     */
    public function GetDataArray()
    {
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

        // dimensions
        if( SpecTiledTool::GetAddDimensions() === true ) {
            array_unshift($data, $this->height, $this->rows);
        }
        
        return $data;
    }

    /**
     * Get screen represented in C
     */
    public function GetC()
    {
        $str = '';

        // add to first screen
        if( $this->num == 0 && Tilemaps::GetNumTilemaps() > 1) {
            $str .= '#define '.Tilemaps::$defineName.' '.Tilemaps::GetNumTilemaps().CR.CR;
        }
        
        // tile numbers
        $str .= SpecTiledTool::GetCArray(
            $this->name, 
            $this->GetDataArray(), 
            10
        ).CR;
        
        return $str;
    }

    /**
     * Get assembly code for this tilemap
     */
    public function GetAsm()
    {
        $str = 'SECTION '.SpecTiledTool::GetCodeSection().CR;
        
        $str .= SpecTiledTool::GetAsmArray(
            $this->name, 
            $this->GetDataArray(), 
            10, 
            8
        ).CR;

        return $str;
    }

}
