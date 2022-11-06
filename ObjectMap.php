<?php

namespace ClebinGames\SpecTiledTool;

/**
 * Class representing an object map
 */
class ObjectMap
{
    private $num = 0;
    private $data = false;
    private $filename = false;
    private $objects = [];
    private $output = [];
    private $customProperties = [];
    private $addDimensions = false;

    public function __construct($num, $layer)
    {
        $this->num = 0;

        // custom properties
        if (isset($layer['properties'])) {
            foreach ($layer['properties'] as $prop) {

                if ($prop['name'] == 'add-dimensions') {
                    if ($prop['value'] === true) {
                        $this->addDimensions = true;
                        echo 'Add object dimensions. ';
                    }
                } else {
                    $this->customProperties[] = $prop['name'];
                }
            }
            if (sizeof($this->customProperties) > 0) {
                echo 'Adding ' . sizeof($this->customProperties) . ' custom properties (' . implode(',', $this->customProperties) . ')' . CR;
            }
        }

        // read objects from layer
        $this->ReadLayerObjects($layer['objects']);
    }

    public function SetData($data)
    {
        $this->data = $data;
    }

    public function SetName($name)
    {
        $this->name = SpecTiledTool::GetConvertedCodeName($name);
        $this->filename = SpecTiledTool::GetConvertedFilename($name);
    }

    public function GetOutputFilename()
    {
        $filename = SpecTiledTool::GetOutputFolder();
        $filename .= $this->filename . '.' . SpecTiledTool::GetOutputFileExtension();

        return $filename;
    }

    /**
     * Read an Tiled object layer
     */
    public function ReadLayerObjects($layer)
    {
        // loop through objects on layer
        foreach ($layer as $json) {

            echo 'Found object "' . $json['name'] . '"' . CR;

            // create new object
            $obj = new GameObject($json);

            // add to array
            $this->objects[] = $obj;
        }

        // loop through objects
        $count = 0;
        foreach ($this->objects as $obj) {
            // add to output array
            $index = $obj->GetIndex();
            if ($index !== false && $index > -1) {
                $this->output[] = $index;
            }

            // add row and column
            $this->output[] = $obj->GetRow();
            $this->output[] = $obj->GetCol();

            // add dimensions
            if ($this->addDimensions === true) {
                $this->output[] = $obj->GetHeight();
                $this->output[] = $obj->GetWidth();
            }

            // add custom properties
            foreach ($this->customProperties as $prop) {
                $this->output[] = $obj->GetCustomProperty($prop);
            }
            if ($count == 0) {
                print_r($this->output);
            }
            $count++;
        }
    }

    /**
     * 
     * Get code for screen in currently set language
     */
    public function GetCode()
    {
        switch (SpecTiledTool::GetFormat()) {
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
        ) . CR;
    }

    public function GetAsm()
    {
        // add array length at the beginning
        array_unshift($this->output, sizeof($this->output));

        $str = 'SECTION ' . SpecTiledTool::GetCodeSection() . CR;

        $str .= SpecTiledTool::GetAsmArray(
            $this->name,
            $this->output,
            10,
            8
        ) . CR;

        return $str;
    }

    // @todo Specify 8-bit layout for custom properties
    // eg. |    -   |   -   |   -   |  -    |  player/computer  |  unit-type   | unit-type  | unit-type  |
}
