<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class AdminMiddleware extends Middleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            $this->redirect('index.php?page=login');
        }
        
        if (Auth::role() !== 'admin') {
            http_response_code(403);
            echo "Access Denied.";
            exit;
        }
    }
}
