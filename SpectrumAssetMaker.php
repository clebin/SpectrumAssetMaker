<?php

require("vendor/autoload.php");

define('CR', "\n");

use ClebinGames\SpectrumAssetMaker\App;

// read filenames from command line arguments
$options = getopt('', [
    'help::',
    'verbosity::',
    'config::',
    'section::',
    'name::'
]);

// run
App::RunCommandLine($options);

echo CR;
