<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Security;
use App\Core\Validator;
use App\Core\Controller;
use App\Models\User;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->render('auth/login');
    }

    public function login(): void
    {
        Security::verifyCsrf();
        
        $validator = new Validator($_POST);
        if (!$validator->validate([
            'email' => 'required|email',
            'password' => 'required'
        ])) {
            $this->render('auth/login', ['error' => $validator->firstError()]);
            return;
        }

        $userModel = new User();
        $email = Security::cleanString($_POST['email']);
        $user = $userModel->findByEmail($email);
        if (!$user || $user['status'] !== 'active' || !password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
            $userModel->logLogin($user['id'] ?? null, $email, 'failed');
            $this->render('auth/login', ['error' => 'Invalid credentials or inactive account.']);
            return;
        }

        Auth::login($user);
        $userModel->touchLastLogin((int) $user['id']);
        $userModel->logLogin((int) $user['id'], $email, 'success');
        Activity::log('Logged in', (int) $user['id']);
        $this->redirect('index.php?page=dashboard');
    }

    public function registerForm(): void
    {
        $this->render('auth/register');
    }

    public function register(): void
    {
        Security::verifyCsrf();

        $validator = new Validator($_POST);
        if (!$validator->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ])) {
            $this->render('auth/register', ['error' => $validator->firstError()]);
            return;
        }

        $name = Security::cleanString($_POST['name'] ?? '');
        $email = $_POST['email'];
        $password = $_POST['password'];

        (new User())->create([
            'role_slug' => 'trainee',
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'phone' => Security::cleanString($_POST['phone'] ?? ''),
            'status' => 'pending',
        ]);
        $this->render('auth/login', ['success' => 'Account registered. An administrator must approve it before login.']);
    }

    public function logout(): void
    {
        Activity::log('Logged out');
        Auth::logout();
        $this->redirect('index.php');
    }
}
