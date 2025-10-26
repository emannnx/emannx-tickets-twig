<?php
namespace App\Controllers;

use App\Utils\Validation;
use App\Utils\Session;

class AuthController {
    private $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }

    public function login() {
        $errors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $errors = Validation::validateLogin(['email' => $email, 'password' => $password]);
            $old = ['email' => $email];

            if (empty($errors)) {
                Session::login($email);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Login successful!'];
                header('Location: https://' . $_SERVER['HTTP_HOST'] . '/dashboard');
                exit;
            }
        }

        echo $this->twig->render('pages/login.html.twig', [
            'errors' => $errors,
            'old' => $old
        ]);
    }

    public function signup() {
        $errors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $errors = Validation::validateSignup([
                'email' => $email,
                'password' => $password,
                'confirm_password' => $confirmPassword
            ]);
            $old = ['email' => $email];

            if (empty($errors)) {
                Session::login($email);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Account created successfully!'];
                header('Location: https://' . $_SERVER['HTTP_HOST'] . '/dashboard');
                exit;
            }
        }

        echo $this->twig->render('pages/signup.html.twig', [
            'errors' => $errors,
            'old' => $old
        ]);
    }

    public function logout() {
        Session::logout();
        header('Location: https://' . $_SERVER['HTTP_HOST'] . '/');
        exit;
    }
}