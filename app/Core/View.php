<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $_view_file_path = dirname(__DIR__) . '/Views/' . $view . '.php';
        $_saved_layout = $layout;
        extract($data, EXTR_SKIP);
        ob_start();
        require $_view_file_path;
        $content = ob_get_clean();

        if ($_saved_layout) {
            $_layout_file_path = dirname(__DIR__) . '/Views/layouts/' . $_saved_layout . '.php';
            require $_layout_file_path;
        } else {
            echo $content;
        }
    }

    public static function partial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/' . $view . '.php';
    }
}

