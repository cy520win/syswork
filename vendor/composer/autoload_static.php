<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5e61abd51d840299fc3c59454694ded6
{
    public static $files = array (
        'c964ee0ededf28c96ebd9db5099ef910' => __DIR__ . '/..' . '/guzzlehttp/promises/src/functions_include.php',
        'a0edc8309cc5e1d60e3047b5df6b7052' => __DIR__ . '/..' . '/guzzlehttp/psr7/src/functions_include.php',
        '37a3dc5111fe8f707ab4c132ef1dbc62' => __DIR__ . '/..' . '/guzzlehttp/guzzle/src/functions_include.php',
        '416fcf0332e9085028e558af9956c971' => __DIR__ . '/../..' . '/Helper/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
        ),
        'G' => 
        array (
            'GuzzleHttp\\Psr7\\' => 16,
            'GuzzleHttp\\Promise\\' => 19,
            'GuzzleHttp\\' => 11,
        ),
        'C' => 
        array (
            'Component\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'GuzzleHttp\\Psr7\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/psr7/src',
        ),
        'GuzzleHttp\\Promise\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/promises/src',
        ),
        'GuzzleHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle/src',
        ),
        'Component\\' => 
        array (
            0 => __DIR__ . '/..' . '/Component/restclient/src',
        ),
    );

    public static $fallbackDirsPsr0 = array (
        0 => __DIR__ . '/../..' . '/',
    );

    public static $classMap = array (
        'Library\\Base' => __DIR__ . '/../..' . '/Library/Base.php',
        'Library\\GetBalance' => __DIR__ . '/../..' . '/Library/GetBalance.php',
        'Library\\MailView' => __DIR__ . '/../..' . '/Library/MailView.php',
        'Library\\Report' => __DIR__ . '/../..' . '/Library/Report.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5e61abd51d840299fc3c59454694ded6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5e61abd51d840299fc3c59454694ded6::$prefixDirsPsr4;
            $loader->fallbackDirsPsr0 = ComposerStaticInit5e61abd51d840299fc3c59454694ded6::$fallbackDirsPsr0;
            $loader->classMap = ComposerStaticInit5e61abd51d840299fc3c59454694ded6::$classMap;

        }, null, ClassLoader::class);
    }
}
