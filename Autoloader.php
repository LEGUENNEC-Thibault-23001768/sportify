<?php
function my_autoloader($class) {
    // Replace namespace separators with directory separators
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    // Prepend the base directory. Adjust this path if necessary.
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $path . '.php';

    if (file_exists($file)) {
        require $file;
    }
}

spl_autoload_register('my_autoloader');