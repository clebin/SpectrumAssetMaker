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

    // number of memory banks
    protected int $numBanks = 1;

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

        // create asm reference file?
        if( isset($config['create-binary-reference-file']) &&
            $config['create-binary-reference-file'] === false) {
                $this->createReferenceFile = false;
        }

        // set name, including code name, define name, etc
        if (isset($config['name']))
            $this->SetName($config['name']);

        // bank
        if( isset($config['bank'])) {
            $this->codeSection = 'BANK_'.intval($config['bank']);
        }
        // page
        if( isset($config['page'])) {
            $this->codeSection = 'PAGE_'.intval($config['page']);
        }
        // code section
        else if (isset($config['section'])) {
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
    public function GetOutputFilename(int $bank = 0) : string
    {
        return $this->filename . ( $this->numBanks > 1 ? '_'.$bank : '' ). 
            '.' . $this->GetOutputFileExtension();
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
    public function GetOutputFilepath(int $bank = 0) : string
    {
        return $this->outputFolder . $this->GetOutputFilename($bank);
    }

    /**
     * Return filepath for an asm reference file
     */
    public function GetOutputReferenceFilepath(int $bank = 0) : string
    {
        return $this->GetOutputFilepath($bank).'.asm';
    }

    /**
     * Set code section (eg. BANK_3)
     */
    public function SetCodeSection($section) : void
    {
        $this->codeSection = $section;
    }

    /**
     * Set code section to next bank or page
     */
    public function SetCodeSectionNextBankOrPage() : void
    {
        if( substr($this->codeSection, 0, 5) == 'PAGE_') {
            $prefix = 'PAGE_';
        }
        else if( substr($this->codeSection, 0, 5) == 'BANK_' ) {
            $prefix = 'BANK_';
        }
        else {
            return;
        }
        $this->codeSection = $prefix.intval(explode('_', $this->codeSection)[1])+1;
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
    public function GetCodeName($bank = 0) : string
    {
        if( $this->numBanks > 1) {
            return App::GetConvertedCodeName($this->name.'-'.$bank, $this->codeFormat);
        }
        return $this->codeName;
    }

    /**
     * Get code section
     */
    public function GetCodeSection() : string
    {
        return $this->codeSection;
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
            $this->GetCodeName(),
            $data,
            10
        ) . CR;

        return $str;
    }

    /**
     * Get code in binary format
     */
    public function WriteBinaryFile($data, $filename, $start = 0, $end = false) : int
    {
        if( $end === false ) {
            $end = sizeof($data);
        }

        // clear file
        file_put_contents($filename, '');

        // add data
        if ($fp = fopen($filename, 'a')) {

            $count = 0;

            // loop throguh data
            for($i=$start;$i<$end;$i++) {

                $value = $data[$i];

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

        // return number of bytes written
        return $count;
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
            $this->GetCodeName(),
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
        // use binaries for zx0 compression
        if ($this->compression == App::COMPRESSION_ZX0) {
            $this->codeFormat = App::FORMAT_BINARY;
        }

        // number of banks
        $this->numBanks = ceil(sizeof($this->data) / App::BANK_LENGTH_BYTES);


         // binary
        if( $this->codeFormat == App::FORMAT_BINARY) {

            $data = $this->GetData();

            // loop through banks
            for($bank=0;$bank<$this->numBanks;$bank++) {

                $dataFilename = $this->GetOutputFilepath($bank);

                // write this section of the binary file
                if( $this->numBanks > 1) {

                    $start = $bank * App::BANK_LENGTH_BYTES;
                    
                    if( $bank < $this->numBanks-1 ) {
                        $end = ($bank+1) * App::BANK_LENGTH_BYTES;
                    } else {
                        $end = sizeof($this->data);
                    }

                    $numBytesWritten = $this->WriteBinaryFile($data, $dataFilename, $start, $end);
                }
                // write the whole thing
                else {
                    $numBytesWritten = $this->WriteBinaryFile($data, $dataFilename);
                }

                // add to .lst file
                if ($this->addToAssetsLst === true) {
                    $this->AddToAssetsLst($bank);
                }

                // create binary reference file
                if( $this->createReferenceFile === true) {
                    
                    $asmReference = $this->GetBinaryReferenceAsmFile($dataFilename, $bank, $numBytesWritten);
                    file_put_contents($this->GetOutputReferenceFilepath($bank), $asmReference);
                }

                // do zx0 compression
                if ($this->compression == App::COMPRESSION_ZX0) {
                    $this->DoZX0Compression($dataFilename, $bank);
                }

                // move to the next bank
                if( $this->numBanks > 0) {
                    $this->SetCodeSectionNextBankOrPage();
                }
            }
        }
        // regular text file
        else {
            file_put_contents($this->GetOutputFilepath(), $this->GetCode());
        }
    }

    public function AddToAssetsLst($bank = 0) : void
    {
        if( $this->codeFormat == App::FORMAT_BINARY) {

            if( $this->createReferenceFile === true) {
                App::AddOutputFile($this->GetOutputReferenceFilepath($bank));
            }

        } else {
            App::AddOutputFile($this->GetOutputFilepath($bank)) . CR;
        }
    }

    public function DoZX0Compression(string $dataFilename, $bank) : void
    {
        // reference file
        if( $this->createReferenceFile === true) {

            $asmReference = $this->GetBinaryReferenceAsmFile($dataFilename, $bank);
            file_put_contents($this->GetOutputReferenceFilepath(), $asmReference);
        }

        // output message
        App::OutputMessage($this->datatypeName, $this->name, 'Compressing ' . $dataFilename . ' with ZX0');

        // do compression
        App::CompressArrayZX0(
            $dataFilename
        );
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
    public function GetBinaryReferenceAsmFile($dataFilename, $bank, $size = 0) : string
    {
        return '; file generated by Spectrum Asset Maker' . CR .
            '; https://github.com/clebin/SpectrumAssetMaker' . CR . CR .
            'section ' . $this->GetCodeSection() . CR . CR .
            'public ' . $this->GetCodeName($bank)  . CR .
            'public ' . $this->GetCodeName($bank).'_end' . CR . CR .
            $this->GetCodeName($bank) . ':' . CR . CR .
            '        BINARY "' . $dataFilename . '"'.($size > 0 ? ' ; '. $size.' bytes' : '') . CR . CR .
            $this->GetCodeName($bank).'_end:' . CR;
    }

    /**
     * Get standard asm header
     */
    public function GetHeaderAsm() : string
    {
        return '; file generated by Spectrum Asset Maker' . CR .
            '; https://github.com/clebin/SpectrumAssetMaker' . CR . CR .
            'section ' . $this->GetCodeSection() . CR;
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
        // check if everything's ok
        if ($this->isValid === true) {
            $this->WriteFile();
        } else {
            App::AddError('Datatype is not valid');
        }
    }
}
