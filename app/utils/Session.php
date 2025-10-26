<?php
namespace App\Utils;

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy() {
        self::start();
        session_destroy();
    }

    public static function isLoggedIn() {
        return !empty(self::get('user_email'));
    }

    public static function login($email) {
        self::set('user_email', $email);
        self::set('logged_in', true);
    }

    public static function logout() {
        self::destroy();
    }
}