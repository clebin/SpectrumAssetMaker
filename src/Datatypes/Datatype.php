<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

abstract class Datatype
{
    public $datatypeName = 'Datatype';
    protected $data = [];
    protected $name;
    protected $codeName;

    // code format - asm or c
    protected $codeFormat = App::FORMAT_ASM;
    protected static $formatsSupported = [
        App::FORMAT_ASM,
        App::FORMAT_C
    ];


    public $compression = App::COMPRESSION_NONE;

    protected $defineName;
    protected $codeSection = 'rodata_user';
    protected $filename = false;
    protected $addArrayLength = true;

    protected $requireInputFile = true;
    protected $inputFilepath = false;

    protected $outputFolder = '';
    protected $isValid = true;
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

        // compression
        if (
            isset($config['compression']) &&
            in_array($config['compression'], App::$compressionSupported)
        ) {
            $this->compression = $config['compression'];
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
        $this->codeName = App::GetConvertedCodeName($name, $this->codeFormat);
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
        if (in_array($format, self::$formatsSupported)) {
            $this->codeFormat = $format;
        } else {
            $this->codeFormat = self::$formatsSupported[0];
        }
    }

    /**
     * Get codename
     */
    public function GetCodeName()
    {
        return $this->codeName;
    }

    /**
     * 
     * Get code for screen in currently set language
     */
    public function GetCode()
    {
        switch ($this->codeFormat) {

            case App::FORMAT_BINARY:
                return $this->GetData();
                break;

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

        $str = $this->GetHeaderC();

        $str .= App::GetCArray(
            $this->codeName,
            $data,
            10
        ) . CR;

        return $str;
    }

    /**
     * Get code in binary format
     */
    public function WriteBinaryFile($filename)
    {
        $data = $this->GetData();

        // clear file
        file_put_contents($filename, '');

        // add data
        if ($fp = fopen($filename, 'a')) {

            $count = 0;
            foreach ($data as $byte) {
                fwrite($fp, pack("C", $byte));
                $count++;
            }
            App::OutputMessage($this->datatypeName, $this->name, 'Wrote ' . $count . ' bytes to binary file.');
        }
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

        $str = $this->GetHeaderAsm();
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

        // compress with zx0
        if ($this->compression == App::COMPRESSION_ZX0) {

            // only output raw data
            $this->codeFormat = App::FORMAT_BINARY;

            // output data
            $data_filename = str_replace('.asm', '.data.asm', $this->GetOutputFilepath());
            $this->WriteBinaryFile($data_filename);

            // output binary header
            file_put_contents($this->getOutputFilepath(), $this->GetBinaryReferenceAsmFile());

            App::OutputMessage($this->datatypeName, $this->name, 'Compressing ' . $data_filename . ' with ZX0');

            App::CompressArrayZX0(
                $data_filename
            );
        }
        // regular - without zx0 compression
        else {
            file_put_contents($this->GetOutputFilepath(), $this->GetCode());
        }
    }

    public function GetHeader()
    {
        switch ($this->codeFormat) {
            case App::FORMAT_C:
                return $this->GetHeaderC();
            default;
                return $this->GetHeaderAsm();
        }
    }

    /**
     * Get C header
     */
    public function GetHeaderC()
    {
        return '// file generated by Spectrum Asset Maker' . CR .
            '// https://github.com/clebin/SpectrumAssetMaker' . CR . CR;
    }

    /**
     * Get header for asm reference to binary file (for zx0)
     */
    public function GetBinaryReferenceAsmFile()
    {
        return '; file generated by Spectrum Asset Maker' . CR .
            '; https://github.com/clebin/SpectrumAssetMaker' . CR . CR .
            'section ' . $this->codeSection . CR . CR .
            'public ' . $this->codeName  . CR .
            '       ' . $this->codeName . ':' . CR .
            '            BINARY "' . $this->GetOutputFilename() . '.zx0"' . CR;
    }

    /**
     * Get standard asm header
     */
    public function GetHeaderAsm()
    {
        return '; file generated by Spectrum Asset Maker' . CR .
            '; https://github.com/clebin/SpectrumAssetMaker' . CR . CR .
            'section ' . $this->codeSection . CR;
    }

    /**
     * Process the file
     */
    public function Process()
    {
        if ($this->isValid === true) {
            $this->WriteFile();
        }
    }
}
