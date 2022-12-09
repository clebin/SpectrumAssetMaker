<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;
use \ClebinGames\SpectrumAssetMaker\GameObject;

/**
 * Class representing an object map
 */
class ObjectMap extends Datatype
{
    protected $num = 0;
    protected $objects = [];
    protected $customProperties = [];
    protected $addDimensions = false;
    protected $addArrayLength = true;
    protected $tilemap;

    public function __construct($tilemap, $num, $layer)
    {
        $this->tilemap = $tilemap;
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
    }

    public function GetData()
    {
        // loop through objects
        $count = 0;
        foreach ($this->objects as $obj) {
            // add to output array
            $index = $obj->GetIndex();

            if ($index !== false && $index > -1) {
                $this->data[] = $index;
            }

            // add row and column
            $this->data[] = $obj->GetRow();
            $this->data[] = $obj->GetCol();

            // add dimensions
            if ($this->addDimensions === true) {
                $this->data[] = $obj->GetHeight();
                $this->data[] = $obj->GetWidth();
            }

            // add custom properties
            foreach ($this->customProperties as $prop) {
                $this->data[] = $obj->GetCustomProperty($prop);
            }
            $count++;
        }

        // if ($count == 0)
        print_r($this->data);

        return $this->data;
    }
}
