<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

abstract class Datatype
{
    protected $data = [];
    protected $name;
    protected $codeName;
    protected $defineName;
    protected $filename = false;
    protected $addArrayLength = true;
    protected $outputFormat = App::FORMAT_ASM;

    protected $outputFormatsSupported = [
        App::FORMAT_C,
        App::FORMAT_ASM
    ];

    public function __construct($name)
    {
        $this->SetName($name);
    }

    public function GetName()
    {
        return $this->name;
    }

    /**
     * Set name and filename
     */
    public function SetName($name)
    {
        $this->name = $name;
        $this->codeName = App::GetConvertedCodeName($name);
        $this->filename = App::GetConvertedFilename($name);
        $this->defineName = App::GetConvertedConstantName($name . '-len');
    }

    /**
     * Set array of data manually
     */
    public function SetData($data)
    {
        $this->data = $data;
    }

    /**
     * Return array of data
     */
    public function GetData()
    {
        return $this->data;
    }

    /**
     * Return output filename only
     */
    public function GetOutputFilename()
    {
        return $this->filename . '.' . App::GetOutputFileExtension();
    }

    /**
     * Return full output filepath
     */
    public function GetOutputFilepath()
    {
        return App::GetOutputFolder() . $this->GetOutputFilename();
    }

    /**
     * 
     * Get code for screen in currently set language
     */
    public function GetCode()
    {
        switch (App::GetFormat()) {
            case App::FORMAT_C:
                return $this->GetC();
                break;

            default:
                return $this->GetAsm();
                break;
        }
    }

    public function GetC()
    {
        return App::GetCArray(
            $this->codeName,
            $this->data,
            10
        ) . CR;
    }

    public function GetAsm()
    {
        // add array length at the beginning
        if ($this->addArrayLength === true) {
            array_unshift($this->data, sizeof($this->data));
        }

        $str = 'SECTION ' . App::GetCodeSection() . CR;

        $str .= App::GetAsmArray(
            $this->codeName,
            $this->GetData(),
            10,
            8
        ) . CR;

        return $str;
    }

    public function WriteFile()
    {
        file_put_contents($this->GetOutputFilepath(), $this->GetCode());
    }

    public function ProcessFile($filename)
    {
        // read tileset graphics
        if ($filename === false) {
            return false;
        }
        $success = $this->ReadFile($filename);

        if ($success === true) {
            $this->WriteFile();
        }
    }
}
