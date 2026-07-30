<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class AuthMiddleware extends Middleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            $this->redirect('index.php?page=login');
        }
    }
}
