<?php
namespace ClebinGames\SpecTiledTool;

/**
 * Class representing an object map
 */
class ObjectMap {

    private $data = false;
    private $filename = false;
    private $objects = [];
    private $output = [];

    public function __construct($layer)
    {
        if( isset($layer['data'])) {
            $this->data = $layer['data'];
        }
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
        $this->name = SpecTiledTool::GetConvertedCodeName($name.'-object-map');
        $this->filename = SpecTiledTool::GetConvertedFilename($name.'-object-map');
    }

    public function GetOutputFilename()
    {
        $filename = SpecTiledTool::GetOutputFolder();
        $filename .= $this->filename.'.'.SpecTiledTool::GetOutputFileExtension();
        
        return $filename;
    }

    /**
     * Read an Tiled object layer (can be enemies, objects or colours)
     */
    public function ReadLayer($layer)
    {
        foreach($data as $json_object) {

            // create new object
            $obj = new GameObject($json_object['type'], $json_obj['y'], $json_obj['x']);

            // name (optional)
            if( $json_object['name'] != '' ) {
                $obj->name = $json_object['name'];
            }

            // add to array
            $this->objects[] = $obj;

            // add to output array
            $this->output[] = $obj->GetIndex();
            $this->output[] = $obj->GetRow();
            $this->output[] = $obj->GetCol();
        }

        return $objects;
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

    public function GetC()
    {
        return SpecTiledTool::GetCArray(
            $this->name, 
            $this->output, 
            10
        ).CR;
    }

    public function GetAsm()
    {
        // add array length at the beginning
        array_unshift($this->output, sizeof($this->output));
        
        $str = 'SECTION '.SpecTiledTool::GetCodeSection().CR;

        $str .= SpecTiledTool::GetAsmArray(
            $this->name, 
            $this->output, 
            2
        ).CR;
        
        return $str;
    }
}
