<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

class Text extends Datatype
{
    public const DATATYPE_NAME = 'Text';

    protected bool $addArrayLength = false;
    protected string|false $filename = '';

    // character codes and delimiters
    protected $linefeed = 13;
    protected $sourceDelimiter = CR;
    protected $asmDelimiter = 0;
    protected $charsetStart = 32;

    public function __construct($config, $data = false)
    {
        parent::__construct($config);

        // use array passed to the block
        if( $data !== false )
        {
            $this->data = $data;
            $this->isValid = true;
        }
        // read from file
        else {
            
            if ($this->inputFilepath === false) {
                $this->isValid = false;
                $this->AddError('No input specified');
                return;
            }

            $this->isValid = $this->ReadFile($this->inputFilepath);
        }

        // asm?
        if( $this->codeFormat == App::FORMAT_ASM ) {
            $this->SetAsmData();
        }
    }

    public function ReadFile($filename)
    {
        // check if filename exists
        if (!file_exists($filename)) {
            $this->AddError('Text file (' . $filename . ') not found');
            $this->isValid = false;
            return false;
        }

        if (App::GetVerbosity() != App::VERBOSITY_SILENT) {
            $this->AddMessage('Reading text file');
        }

        // get contents
        $strData = file_get_contents($filename);

        // delimiter
        $this->sourceDelimiter = App::GetStringDelimiter();

        $this->data = explode($this->sourceDelimiter, $strData);
 
        return true;
    }

    public function SetAsmData()
    {
        $asmData = [];

        foreach($this->data as $string) {

            for( $i = 0;$i<strlen($string);$i++) {

                // regular charset
                if (in_array($string[$i], App::$charset)) {
                    $asmData[] = $this->charsetStart + array_search($string[$i], App::$charset);
                }
            }

            $asmData[] = $this->asmDelimiter;  
        }

        $this->data = $asmData;
    }

    public function GetCodeC()
    {
        $output = $this->GetHeaderC();
        $output .= 'char *' . $this->codeName . '[] = {' . CR;

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
