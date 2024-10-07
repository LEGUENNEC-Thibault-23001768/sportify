<?php

namespace Core;

class View
{
    private $viewPath;

    public function __construct($viewPath = '../src/Views/')
    {
        $this->viewPath = rtrim($viewPath, '/') . '/';
    }

    public function render($view, $data = [])
    {
        $filePath = $this->viewPath . str_replace('.', '/', $view) . '.php';


        if (!file_exists($filePath)) {
            throw new \Exception("View file not found: $filePath");
        }

        extract($data);

        ob_start();

        include $filePath;

        $content = ob_get_clean();

        return $content;
    }

    public function renderWithLayout($view, $layout, $data = [])
    {
        $content = $this->render($view, $data);
        return $this->render($layout, array_merge($data, ['content' => $content]));
    }
}