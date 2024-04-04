<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class BlankData extends Datatype
{
    protected $size = 0;
    protected $requireInputFile = false;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->size = intval($config['size']);
        $this->addArrayLength = false;

        if ($this->size > 0)
            $this->isValid = true;
    }

    public function Process()
    {
        if ($this->isValid === true) {
            $this->data = array_fill(0, $this->size, 0);
            $this->WriteFile();
        }
    }
}
