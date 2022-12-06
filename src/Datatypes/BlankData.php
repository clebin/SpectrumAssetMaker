<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class BlankData extends Datatype
{
    public function Process($size)
    {
        $this->data = array_fill(0, $size, 0);
        $this->WriteFile();
    }
}
