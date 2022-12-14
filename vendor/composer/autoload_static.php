<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8c9191ebb97d30aad9a5798efc84c45d
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Container\\' => 14,
        ),
        'L' => 
        array (
            'Laminas\\Stdlib\\' => 15,
            'Laminas\\Config\\' => 15,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'Laminas\\Stdlib\\' => 
        array (
            0 => __DIR__ . '/..' . '/laminas/laminas-stdlib/src',
        ),
        'Laminas\\Config\\' => 
        array (
            0 => __DIR__ . '/..' . '/laminas/laminas-config/src',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8c9191ebb97d30aad9a5798efc84c45d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8c9191ebb97d30aad9a5798efc84c45d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8c9191ebb97d30aad9a5798efc84c45d::$classMap;

        }, null, ClassLoader::class);
    }
}
