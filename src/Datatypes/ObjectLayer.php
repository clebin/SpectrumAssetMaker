<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;
use \ClebinGames\SpectrumAssetMaker\GameObject;

/**
 * Class representing an object map
 */
class ObjectLayer extends Datatype
{
    public const DATATYPE_NAME = 'Object Layer';
    protected int $num = 0;
    protected array $objects = [];
    protected array $customProperties = [];
    protected bool $addObjectDimensions = false;
    protected bool $addArrayLength = true;
    protected array|false $objectTypes = [];
    protected array $layer = [];

    protected $tilemap;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->tilemap = $config['tilemap'];
        $this->num = $config['num'];
        $this->layer = $config['layer'];
        $this->objectTypes = $config['object-types'];
        $this->outputFolder = $config['output-folder'];
        $this->codeFormat = $config['format'];

        // custom properties
        if (isset($this->layer['properties'])) {
            foreach ($this->layer['properties'] as $prop) {

                if ($prop['name'] == 'add-dimensions') {

                    if ($prop['value'] === true) {

                        $this->addObjectDimensions = true;

                        if (App::GetVerbosity() == App::VERBOSITY_VERBOSE) {
                            echo 'Adding object dimensions. ' . CR;
                        }
                    }
                } else {
                    $this->customProperties[] = $prop['name'];
                }
            }

            // if (sizeof($this->customProperties) > 0) {
            //     echo 'Adding ' . sizeof($this->customProperties) . ' custom properties (' . implode(',', $this->customProperties) . ')' . CR;
            // }
        }

        // read objects from layer
        $this->ReadLayerObjects($this->layer['objects']);
    }

    /**
     * Read an Tiled object layer
     */
    public function ReadLayerObjects($layer)
    {
        // loop through objects on layer
        foreach ($layer as $json) {

            // create new object
            $obj = new GameObject($json);

            if (App::GetVerbosity() == App::VERBOSITY_VERBOSE) {
                echo 'Found object "' . $json['name'] . '" (' . $obj->GetIndex() . ')' . CR;
            }

            // add to array
            $this->objects[] = $obj;
        }
    }

    public function GetData() : array
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
            if ($this->addObjectDimensions === true) {
                $this->data[] = $obj->GetHeight();
                $this->data[] = $obj->GetWidth();
                // echo CR . CR . $index . ': ' . $obj->GetRow() . ',' . $obj->GetCol() . ',' . $obj->GetHeight() . ',' . $obj->GetWidth() . CR . CR;
            }

            // add custom properties
            foreach ($this->customProperties as $prop) {
                $this->data[] = $obj->GetCustomProperty($prop);
            }
            $count++;
        }

        // if ($count == 0)
        // print_r($this->data);

        return $this->data;
    }
}
