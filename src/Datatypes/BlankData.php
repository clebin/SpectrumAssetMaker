<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class BlankData extends Datatype
{
    public string $datatypeName = 'Blank Data';
    
    protected int $size = 0;
    protected bool $requireInputFile = false;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->size = intval($config['size']);
        $this->addArrayLength = false;

        if ($this->size > 0) {
            $this->isValid = true;
            $this->data = array_fill(0, $this->size, 0);
        }

        if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
            App::OutputMessage($this->datatypeName, $this->name, 'Allocating size '.$this->size.' bytes');
        }
    }
}
