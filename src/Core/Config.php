<?php

namespace Core;

class Config
{
    private static $config;

    /**
     * @param $file
     * @return void
     */
    public static function load($file): void
    {
        self::$config = require $file;
    }

    /**
     * @param $key
     * @param $default
     * @return mixed|null
     */
    public static function get($key, $default = null): mixed
    {
        return self::$config[$key] ?? $default;
    }
}