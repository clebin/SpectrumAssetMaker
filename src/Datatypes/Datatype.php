<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

abstract class Datatype
{
    protected $data = [];
    protected $name;
    protected $codeName;
    protected $codeFormat = App::FORMAT_ASM;
    protected $defineName;
    protected $codeSection = 'rodata_user';
    protected $filename = false;
    protected $addArrayLength = true;

    protected $requireInputFile = true;
    protected $inputFilepath = false;

    protected $outputFolder = '';
    protected $isValid = false;
    protected $addToAssetsLst = true;

    public function __construct($config)
    {
        // input filename
        if (isset($config['input'])) {
            $this->inputFilepath = $config['input'];

            if (!file_exists($this->inputFilepath)) {
                $this->isValid = false;
            }
        }
        // input filename not set, but required
        else if ($this->requireInputFile === true) {
            $this->isValid = false;
        }

        // set name, including code name, define name, etc
        if (isset($config['name']))
            $this->SetName($config['name']);

        // code section
        if (isset($config['section'])) {
            $this->SetCodeSection($config['section']);
        }

        // output format
        if (isset($config['format'])) {
            $this->SetFormat($config['format']);
        }

        // output folder
        if (isset($config['output-folder'])) {
            $this->outputFolder = rtrim($config['output-folder'], '/') . '/';
        } else {
            $this->outputFolder = '';
        }
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
     * 
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
        return $this->filename . '.' . $this->GetOutputFileExtension();
    }


    /**
     * Get output file extension for the current format/language
     */
    public function GetOutputFileExtension()
    {
        switch ($this->codeFormat) {
            case 'c':
                return 'c';
                break;

            default:
                return 'asm';
        }
    }

    /**
     * Return full output filepath
     */
    public function GetOutputFilepath()
    {
        return $this->outputFolder . $this->GetOutputFilename();
    }

    /**
     * Set code section (eg. BANK_3)
     */
    public function SetCodeSection($section)
    {
        $this->codeSection = $section;
    }

    /**
     * Set whether to output in C or Assembly
     */
    public function SetFormat($format)
    {
        if (in_array($format, App::$formatsSupported)) {
            $this->codeFormat = $format;
        }
    }

    /**
     * 
     * Get code for screen in currently set language
     */
    public function GetCode()
    {
        switch ($this->codeFormat) {
            case App::FORMAT_C:
                return $this->GetCodeC();
                break;

            default:
                return $this->GetCodeAsm();
                break;
        }
    }

    /**
     * Get code in C format
     */
    public function GetCodeC()
    {
        $data = $this->GetData();

        return App::GetCArray(
            $this->codeName,
            $data,
            10
        ) . CR;
    }

    /**
     * Get code in assembly format
     */
    public function GetCodeAsm()
    {
        $data = $this->GetData();

        // add array length at the beginning
        if ($this->addArrayLength === true) {
            array_unshift($data, sizeof($data));
        }

        $str = 'SECTION ' . $this->codeSection . CR;

        $str .= App::GetAsmArray(
            $this->codeName,
            $data,
            10,
            8
        ) . CR;

        return $str;
    }

    /**
     * Write to output file
     */
    public function WriteFile()
    {
        if ($this->addToAssetsLst === true) {
            App::AddOutputFile($this->GetOutputFilepath()) . CR;
        }

        file_put_contents($this->GetOutputFilepath(), $this->GetCode());
    }

    /**
     * 
     */
    public function Process()
    {
        $this->WriteFile();
    }

    /**
     * Process input file
     */
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
