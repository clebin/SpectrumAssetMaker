<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class ArrayData extends Datatype
{
    public $datatypeName = 'Array Data';

    public function __construct($config)
    {
        parent::__construct($config);

        if ($this->inputFilepath === false) {
            $this->isValid = false;
            App::AddError($this->datatypeName . ': No input specified for "' . $this->name . '"');
            return;
        }

        // read json
        $json = file_get_contents($this->inputFilepath);

        try {
            $config = json_decode($json, true);
        } catch (\Exception $e) {
            echo 'Error reading JSON:' . $e;
            App::AddError($this->datatypeName . ': Error parsing JSON for ' . $this->name . '"');
            $this->isValid = false;
            return;
        }
    }
}
