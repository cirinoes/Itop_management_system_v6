<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    private static ?\Twig\Environment $twig = null;

    private static function getTwig(): \Twig\Environment
    {
        if (self::$twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/Views');
            self::$twig = new \Twig\Environment($loader, [
                'cache' => false,
            ]);
            
            self::$twig->addGlobal('auth_check', \App\Core\Auth::check());
            self::$twig->addGlobal('auth_role', \App\Core\Auth::role());
            self::$twig->addGlobal('auth_name', \App\Core\Auth::check() ? (\App\Core\Auth::user()['name'] ?? '') : '');
            self::$twig->addGlobal('auth_user', \App\Core\Auth::user());
            self::$twig->addGlobal('APP_URL', defined('APP_URL') ? APP_URL : '');
            self::$twig->addGlobal('APP_NAME', defined('APP_NAME') ? APP_NAME : 'ITOP Management System');
            self::$twig->addGlobal('current_year', date('Y'));
            self::$twig->addGlobal('csrf_token', \App\Core\Security::csrfToken());

            if (\App\Core\Auth::check()) {
                $userId = (int) \App\Core\Auth::id();
                self::$twig->addGlobal('messageCount', (new \App\Models\Message())->unreadCount($userId));
                self::$twig->addGlobal('notificationCount', (new \App\Models\Notification())->unreadCount($userId));
                self::$twig->addGlobal('recentNotifications', (new \App\Models\Notification())->recent($userId));
                self::$twig->addGlobal('userTheme', \App\Core\Auth::user()['theme_preference'] ?? 'light');
            } else {
                self::$twig->addGlobal('userTheme', 'light');
            }

            $footerSettings = [];
            try {
                $db = \App\Core\Model::getDb();
                $stmt = $db->query('SELECT setting_key, setting_value FROM website_settings');
                foreach ($stmt->fetchAll() as $row) {
                    $footerSettings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (\Exception $e) {}
            self::$twig->addGlobal('footerSettings', $footerSettings);
        }
        return self::$twig;
    }

    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewsPath = dirname(__DIR__) . '/Views/';
        
        // 1. If a Twig template exists, use the Twig engine
        if (file_exists($viewsPath . $view . '.twig')) {
            echo self::getTwig()->render($view . '.twig', $data);
            return;
        }

        // 2. Legacy PHP fallback
        $_view_file_path = $viewsPath . $view . '.php';
        $_saved_layout = $layout;
        extract($data, EXTR_SKIP);
        ob_start();
        require $_view_file_path;
        $content = ob_get_clean();

        if ($_saved_layout) {
            $_layout_file_path = $viewsPath . 'layouts/' . $_saved_layout . '.php';
            require $_layout_file_path;
        } else {
            echo $content;
        }
    }

    public static function partial(string $view, array $data = []): void
    {
        $viewsPath = dirname(__DIR__) . '/Views/';
        if (file_exists($viewsPath . $view . '.twig')) {
            echo self::getTwig()->render($view . '.twig', $data);
            return;
        }

        extract($data, EXTR_SKIP);
        require $viewsPath . $view . '.php';
    }
}
