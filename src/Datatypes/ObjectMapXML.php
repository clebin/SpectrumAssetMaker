<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;
use \ClebinGames\SpectrumAssetMaker\GameObject;

/**
 * Class representing an object map
 */
class ObjectMapXML extends ObjectMap
{
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
        $this->ReadLayerObjects($layer['object']);
    }

    /**
     * Read an Tiled object layer
     */
    public function ReadLayerObjects($layer)
    {
        // loop through objects on layer
        foreach ($layer as $objdata) {

            if (isset($objdata['@attributes'])) {
                $data = $objdata['@attributes'];

                // parse properties
                if (isset($objdata['properties'])) {
                    $data['properties'] = [];
                    foreach ($objdata['properties']['property'] as $prop) {

                        if (isset($prop['@attributes'])) {
                            $data['properties'][] = $prop['@attributes'];
                        } else {
                            $data['properties'][] = $prop;
                        }
                    }
                }
            } else {
                $data = $objdata;
            }

            echo 'Found object "' . $data['name'] . '"' . CR;

            // create new object
            $obj = new GameObject($data);

            // add to array
            $this->objects[] = $obj;
        }
    }
}
