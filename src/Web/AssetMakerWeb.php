<?php

namespace ClebinGames\SpectrumAssetMaker\Web;

use ClebinGames\SpectrumAssetMaker\App;

class AssetMakerWeb
{
    private static $twig;

    public static function Initialise()
    {
        // initialise twig
        $loader = new \Twig\Loader\FilesystemLoader('../templates');

        self::$twig = new \Twig\Environment($loader, [
            'cache' => '../twig_cache',
            'auto_reload' => true
        ]);
    }

    public static function OutputPage()
    {
        echo self::$twig->render('page.html.twig', [

        ]);
    }
}
