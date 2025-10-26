<?php
namespace App\Utils;

class Auth {
    public static function check() {
        return Session::isLoggedIn();
    }

    public static function user() {
        return Session::get('user_email');
    }

    public static function requireAuth() {
        if (!self::check()) {
            header('Location: /auth/login');
            exit;
        }
    }

    public static function requireGuest() {
        if (self::check()) {
            header('Location: /dashboard');
            exit;
        }
    }
}