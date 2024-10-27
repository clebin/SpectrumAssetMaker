<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class ArrayData extends Datatype
{
    public $datatypeName = 'Array Data';

    public $arrayName = '';

    public $data = [];
    public $stringData = [];
    public $fields = [];
    public $numFields = 0;

    public function __construct($config)
    {
        parent::__construct($config);

        // field config
        $this->fields = $config['fields'];
        $this->numFields = sizeof($this->fields);

        $this->isValid = $this->ReadFile($this->inputFilepath);
    }

    public function ReadFile($filename)
    {
        if ($this->inputFilepath === false) {
            App::AddError($this->datatypeName . ': No input specified for "' . $this->name . '"');
            return false;
        }

        // read data
        if( !file_exists($this->inputFilepath)) {
            App::AddError($this->datatypeName.': File not found for "'.$this->name.'"');    
            return false;
        }

        $strData = file_get_contents($this->inputFilepath);
        $strData = trim($strData, "\n");
        $this->data = explode(App::GetStringDelimiter(), $strData);

        // check if data matches number of fields
        if( sizeof($this->data) % $this->numFields > 0 ) {
            App::AddError($this->datatypeName.': Data size mismatched to number of fields for "'.$this->name.'"');
            return false;
        }
        
        return true;
    }

    public function WriteFile()
    {
        if ($this->addToAssetsLst === true) {
            App::AddOutputFile($this->GetOutputFilepath()) . CR;
        }

        // loop through lines of the file
        for($i=0;$i<sizeof($this->data);$i++)
        {
            $field = $this->fields[$i % $this->numFields];

            switch($field['type'])
            {
                case 'string':

                    // pad string to specified length
                    if( isset($field['size'])) {

                    }
                    // save in a separate array
                    else {

                        // create new array
                        if( !isset($stringData[$field['name']])) {
                            $this->stringData[$field['name']] = [];
                        }
                        // add to array
                        $this->stringData[$field['name']][] = $this->data[$i];
                    }

                    break;

                case 'uint8_t':
                    break;

                case 'int8_t':

                    break;

                case 'uint16_t':
                    
                    break;

                case 'int16_t':

                    break;
            }
        }

        // save strings in a separate file
        foreach($this->stringData as $name => $data)
        {
            (new Text([
                'name' => $name,
                'output-folder' => $this->outputFolder
            ], $data))->Process();
        }

        return true;
    }
}
