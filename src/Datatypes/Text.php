<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class Text extends Datatype
{
    private $delimeter = 144; // 13=linefeed, 144=first udg
    private $charsetStart = 32;
    private $charset = [
        ' ', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        ':', ';', '<', '=', '>', '?', '@',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        '[', ']', '^', '_', '£',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
    ];

    public function ReadFile($filename)
    {
        $strData = file_get_contents($filename);

        for ($i = 0; $i < strlen($strData); $i++) {

            if (in_array($strData[$i], $this->charset)) {
                $this->data[] = $this->charsetStart + array_search($strData[$i], $this->charset);
            }
            // line-feed
            else if ($strData[$i] == CR) {
                $this->data[] = $this->delimeter;
            }
        }
        print_r($this->data);

        return true;
    }
}
