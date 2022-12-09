<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class Text extends Datatype
{
    protected $linefeed = 13;
    protected $sourceDelimiter = CR;
    protected $asmDelimiter = 0;
    protected $charsetStart = 32;
    protected $addArrayLength = false;

    protected $charset = [
        ' ', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        ':', ';', '<', '=', '>', '?', '@',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        '[', ']', '\\', '^', '_', '£',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
    ];

    public function ReadFile($filename)
    {
        $strData = file_get_contents($filename);

        $this->sourceDelimiter = App::GetStringDelimiter();

        // c
        if (App::GetFormat() == App::FORMAT_C) {
            $this->data = explode($this->sourceDelimiter, $strData);
        }
        // assembly
        else {
            for ($i = 0; $i < strlen($strData); $i++) {

                // regular charset
                if (in_array($strData[$i], $this->charset)) {
                    $this->data[] = $this->charsetStart + array_search($strData[$i], $this->charset);
                }
                // delimiter (default is line-feed)
                else if ($strData[$i] == $this->sourceDelimiter) {
                    $this->data[] = $this->asmDelimiter; // add \0
                }
                // line-feed (when not used as delimiter)
                else if ($strData[$i] == CR) {
                    $this->data[] = $this->linefeed;
                }
            }
        }

        // add delimiter to the end
        if ($this->data[sizeof($this->data)] != $this->asmDelimiter) {
            $this->data[] = $this->asmDelimiter;
        }

        return true;
    }

    public function GetCodeC()
    {
        $output = 'char *' . $this->codeName . '[] = {' . CR;

        for ($i = 0; $i < sizeof($this->data); $i++) {
            $output .= '    "' . $this->data[$i] . '"';
            if ($i < sizeof($this->data) - 1) {
                $output .= ',' . CR;
            }
        }
        $output .= '};' . CR;

        return $output;
    }
}
