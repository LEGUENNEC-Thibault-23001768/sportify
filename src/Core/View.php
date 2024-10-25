<?php

namespace Core;

class View
{
    private static $viewPath;

    public static function init()
    {
        self::$viewPath = Config::get("view_path", '../src/Views/');
    }

    public static function render($view, $data = [])
    {
        $filePath = self::$viewPath . str_replace('.', '/', $view) . '.php';

        if (!file_exists($filePath)) {
            throw new \Exception("View file not found: $filePath");
        }

        extract($data);

        ob_start();

        include $filePath;

        $content = ob_get_clean();

        return $content;
    }

    public static function renderWithLayout($view, $layout, $data = [])
    {
        $content = self::render($view, $data);
        return self::render($layout, array_merge($data, ['content' => $content]));
    }
}