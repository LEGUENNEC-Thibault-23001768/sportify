<?php

require_once __DIR__ . '/vendor/autoload.php'; // autoloader de composer
function my_autoloader($class) {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $path . '.php';

    if (file_exists($file)) {
        require $file;
    }
}

spl_autoload_register('my_autoloader');