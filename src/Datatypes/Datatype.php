<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

abstract class Datatype
{
    // datatype friendly name
    public string $datatypeName = 'Datatype';

    // config
    public array $config = [];

    // data
    protected array $data = [];

    // output name
    protected string $name = '';

    // output name for code
    protected string $codeName;

    // static define name for code
    protected string $defineName;

    // output format - asm, c or binary
    protected string $codeFormat = App::FORMAT_ASM;

    // support compression tyhpes
    protected static array $formatsSupported = [
        App::FORMAT_ASM,
        App::FORMAT_C,
        App::FORMAT_BINARY
    ];

    // create an asm reference file for binaries?
    public bool $createReferenceFile = true;

    // generated binary file size
    public int $generatedBinaryFilesize = 0;

    // compression
    public string|false $compression = App::COMPRESSION_NONE;

    // code section
    protected string $codeSection = 'rodata_user';

    // input filename
    protected string|false $filename = false;

    // add array length to beginning of asm array
    protected bool $addArrayLength;

    // default file extension for binary file
    public string $binaryFileExtension = 'bin';

    // do we need an input file for this datatype?
    protected bool $requireInputFile = true;

    // input filepath
    protected string|false $inputFilepath = false;

    // output folder
    protected string $outputFolder = '';

    // add to project .lst file
    protected bool $addToAssetsLst = true;

    // is everything valid for processing?
    protected bool $isValid = true;

    public function __construct($config)
    {
        $this->config = $config;

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

        // don't add to assets lst file
        if( isset($config['add-to-assets-list']) && $config['add-to-assets-list'] === false) {
            $this->addToAssetsLst = false;
        }

        // binary reference file
        if( isset($config['create-reference-file']) && $config['create-reference-file'] === false) {
            $this->createReferenceFile = false;
        }

        // compression
        if (
            isset($config['compression']) &&
            in_array($config['compression'], App::$compressionSupported)
        ) {
            $this->compression = $config['compression'];
        }
    }

    public function GetImageFromFile($filename) : \GdImage|false
    {
        // read image file
        $file_extension = substr($filename, -3);

        if ($file_extension == App::FILE_EXTENSION_PNG) {
            $image = imagecreatefrompng($filename);
        } else if ($file_extension == App::FILE_EXTENSION_GIF) {
            $image = imagecreatefromgif($filename);
        } else {
            App::AddError('Filetype (' . $file_extension . ') not supported');
            return false;
        }

        return $image;
    }

    public function GetName() : string
    {
        return $this->name;
    }

    /**
     * Set name and filename
     */
    public function SetName($name) : void
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
    public function SetData($data) : void
    {
        $this->data = $data;
    }

    /**
     * Return array of data
     */
    public function GetData() : array
    {
        return $this->data;
    }

    /**
     * Return output filename only
     */
    public function GetOutputFilename() : string
    {
        return $this->filename . '.' . $this->GetOutputFileExtension();
    }

    /**
     * Get output file extension for the current format/language
     */
    public function GetOutputFileExtension() : string
    {
        switch ($this->codeFormat) {
            case 'c':
                return 'c';
                break;

            case 'binary':
                return $this->GetBinaryFileExtension();
            break;

            default:
                return 'asm';
        }
    }

    /**
     * Return full output filepath
     */
    public function GetOutputFilepath() : string
    {
        return $this->outputFolder . $this->GetOutputFilename();
    }

    /**
     * Return filepath for an asm reference file
     */
    public function GetOutputReferenceFilepath() : string
    {
        return $this->GetOutputFilepath().'.asm';
    }

    /**
     * Set code section (eg. BANK_3)
     */
    public function SetCodeSection($section) : void
    {
        $this->codeSection = $section;
    }

    /**
     * Set whether to output in C or Assembly
     */
    public function SetFormat($format) : void
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
    public function GetCodeName() : string
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
    public function WriteBinaryFile($filename) : void
    {
        $data = $this->GetData();

        // clear file
        file_put_contents($filename, '');

        // add data
        if ($fp = fopen($filename, 'a')) {

            $count = 0;

            // loop throguh data
            foreach ($data as $value) {

                // value is an array - eg. array split into attributes
                if( is_array($value)) {

                    // loop through array
                    foreach($value as $datarow) {

                        $byte = intval(implode('', $datarow));
                        fwrite($fp, pack("C", $byte));
                        $count++;
                    }
                }
                // write value
                else {
                    fwrite($fp, pack("C", $value));
                    $count++;
                }

            }
            App::OutputMessage($this->datatypeName, $this->name, 'Wrote ' . $count . ' bytes to binary file.');
        }

        $this->generatedBinaryFilesize = $count;
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
    public function WriteFile() : void
    {
        if ($this->addToAssetsLst === true) {

            if( $this->codeFormat == App::FORMAT_BINARY) {

                if( $this->createReferenceFile === true) {
                    App::AddOutputFile($this->GetOutputReferenceFilepath());
                }

            } else {
                App::AddOutputFile($this->GetOutputFilepath()) . CR;
            }
        }

        // use binaries for zx0 compression
        if ($this->compression == App::COMPRESSION_ZX0) {
            $this->codeFormat = App::FORMAT_BINARY;
        }

        // binary
        if( $this->codeFormat == App::FORMAT_BINARY) {

            $data_filename = $this->GetOutputFilepath();
            
            $this->WriteBinaryFile($data_filename);

            // create binary reference file
            if( $this->createReferenceFile === true) {
                
                $asmReference = $this->GetBinaryReferenceAsmFile($data_filename, $this->generatedBinaryFilesize);
                file_put_contents($this->GetOutputReferenceFilepath(), $asmReference);
            }
        }
        // regular text file
        else {
            file_put_contents($this->GetOutputFilepath(), $this->GetCode());
        }
        
        // do zx0 compression
        if ($this->compression == App::COMPRESSION_ZX0) {

            $compressed_filename = $data_filename.'.zx0';

            // reference file
            if( $this->createReferenceFile === true) {

                $asmReference = $this->GetBinaryReferenceAsmFile($data_filename);
                file_put_contents($this->GetOutputReferenceFilepath(), $asmReference);
            }

            // output message
            App::OutputMessage($this->datatypeName, $this->name, 'Compressing ' . $data_filename . ' with ZX0');

            // do compression
            App::CompressArrayZX0(
                $data_filename
            );
        }
    }

    public function GetHeader() : string
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
    public function GetHeaderC() : string
    {
        return '// file generated by Spectrum Asset Maker' . CR .
            '// https://github.com/clebin/SpectrumAssetMaker' . CR . CR;
    }

    /**
     * Get header for asm reference to binary file (for zx0)
     */
    public function GetBinaryReferenceAsmFile($data_filename, $size = 0) : string
    {
        return '; file generated by Spectrum Asset Maker' . CR .
            '; https://github.com/clebin/SpectrumAssetMaker' . CR . CR .
            'section ' . $this->codeSection . CR . CR .
            'public ' . $this->codeName  . CR .
            'public ' . $this->codeName.'_end' . CR . CR .
            $this->codeName . ':' . CR . CR .
            '        BINARY "' . $data_filename . '"'.($size > 0 ? ' ; '. $size.' bytes' : '') . CR . CR .
            $this->codeName.'_end:' . CR;
    }

    /**
     * Get standard asm header
     */
    public function GetHeaderAsm() : string
    {
        return '; file generated by Spectrum Asset Maker' . CR .
            '; https://github.com/clebin/SpectrumAssetMaker' . CR . CR .
            'section ' . $this->codeSection . CR;
    }

    /**
     * Get the file extension for a binary file
     */
    public function GetBinaryFileExtension() : string
    {
        return $this->binaryFileExtension;
    }
    
    /**
     * Process the file
     */
    public function Process() : void
    {
        if ($this->isValid === true) {
            $this->WriteFile();
        }
    }
}
