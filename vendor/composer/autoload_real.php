<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit1453d6c2f99df877ea02a364b74b0d00
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit1453d6c2f99df877ea02a364b74b0d00', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit1453d6c2f99df877ea02a364b74b0d00', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit1453d6c2f99df877ea02a364b74b0d00::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
