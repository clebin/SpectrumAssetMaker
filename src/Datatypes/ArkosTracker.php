<?php

namespace ClebinGames\SpectrumAssetMaker\Datatypes;

use \ClebinGames\SpectrumAssetMaker\App;

// Usage:
// -p $(ARKOS_PATH) -s _song -m "BANK_1" $(ROOT_PATH)/raw-assets/music/menu-music-2.aks 
//> $(ROOT_PATH)/assets/music/menu-music.asm

class ArkosTracker extends Datatype
{
    protected static $arkosCommands = [
        'SongToAkg',
        'SongToSoundEffects'
    ];

    protected $arkosPath = false;
    protected $arkosCommand = 'SongToAkg';
    protected $arkosCommandPath = 'SongToAkg';

    protected $inputFilepath;
    protected $numEffects = 0;

    public function __construct($config)
    {
        parent::__construct($config);

        // arkos command
        if (
            isset($config['command']) &&
            in_array($config['command'], self::$arkosCommands)
        ) {
            $this->arkosCommand = $config['command'];
        }

        // path
        if (isset($config['arkos-path'])) {
            $this->arkosPath = rtrim($config['arkos-path'], '/') . '/tools/';
        }

        // command path
        $this->arkosCommandPath = $this->arkosPath . $this->arkosCommand;

        $this->ReadFile($this->inputFilepath);
    }

    /**
     * Read and process input type
     */
    public function ReadFile($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }

        $command = '"' . $this->arkosCommandPath . '" "' . $filename . '" "' . $this->GetOutputFilepath() . '"';

        echo 'Running command: ' . $command . CR;

        // run command
        shell_exec($command);
    }

    /**
     * Return tileset in assembly format
     */
    public function GetCode()
    {
        $str = 'section ' . $this->codeSection . CR;
        $str .= 'public ' . $this->codeName . CR;
        $str .= $this->codeName . ':' . CR;

        $str .= file_get_contents($this->GetOutputFilepath());

        return $str;
    }
}
