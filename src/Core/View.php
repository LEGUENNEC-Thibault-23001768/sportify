<?php

namespace Core;

use Exception;

class View
{
    private static string $viewPath;

    /**
     * @return void
     */
    public static function init(): void
    {
        self::$viewPath = Config::get("view_path", '../src/Views/');
    }

    /**
     * @param $view
     * @param $layout
     * @param array $data
     * @return false|string
     * @throws Exception
     */
    public static function renderWithLayout($view, $layout, array $data = []): false|string
    {
        $content = self::render($view, $data);
        return self::render($layout, array_merge($data, ['content' => $content]));
    }

    /**
     * @param $view
     * @param array $data
     * @return false|string
     * @throws Exception
     */
    public static function render($view, array $data = []): false|string
    {
        $filePath = self::$viewPath . str_replace('.', '/', $view) . '.php';

        if (!file_exists($filePath)) {
            throw new Exception("View file not found: $filePath");
        }

        extract($data);

        ob_start();

        include $filePath;

        return ob_get_clean();
    }
}