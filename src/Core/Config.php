<?php

namespace Core;

class Config
{
    private static $config;

    public static function load($file)
    {
        self::$config = require $file;
    }

    public static function get($key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }
}