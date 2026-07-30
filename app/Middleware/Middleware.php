<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

abstract class Middleware
{
    abstract public function handle(): void;
    
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
