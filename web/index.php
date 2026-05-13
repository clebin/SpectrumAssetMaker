<?php


require("../vendor/autoload.php");

define('CR', "\n");

use ClebinGames\SpectrumAssetMaker\Web\AssetMakerWeb;

AssetMakerWeb::Initialise();

AssetMakerWeb::OutputPage();
