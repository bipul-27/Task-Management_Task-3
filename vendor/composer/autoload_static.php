<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit53f4ee774dfa778f0261b6480385d154
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPFluent\\' => 9,
        ),
        'F' => 
        array (
            'FluentPlugin\\Framework\\' => 23,
            'FluentPlugin\\App\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPFluent\\' => 
        array (
            0 => __DIR__ . '/..' . '/wpfluent/framework/src/WPFluent',
        ),
        'FluentPlugin\\Framework\\' => 
        array (
            0 => __DIR__ . '/..' . '/wpfluent/framework/src/WPFluent',
        ),
        'FluentPlugin\\App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'FluentPlugin\\Database\\DBMigrator' => __DIR__ . '/../..' . '/database/DBMigrator.php',
        'FluentPlugin\\Database\\DBSeeder' => __DIR__ . '/../..' . '/database/DBSeeder.php',
        'FluentPlugin\\Database\\Migrations\\ExampleMigrator' => __DIR__ . '/../..' . '/database/Migrations/ExampleMigrator.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit53f4ee774dfa778f0261b6480385d154::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit53f4ee774dfa778f0261b6480385d154::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit53f4ee774dfa778f0261b6480385d154::$classMap;

        }, null, ClassLoader::class);
    }
}
