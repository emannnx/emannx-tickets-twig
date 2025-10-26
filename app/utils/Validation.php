<?php
namespace App\Utils;

class Validation {
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePassword($password) {
        return strlen($password) >= 6;
    }

    public static function validateTicket($data) {
        $errors = [];

        if (empty(trim($data['title']))) {
            $errors['title'] = "Title is required.";
        }

        if (!in_array($data['status'], ['open', 'in_progress', 'closed'])) {
            $errors['status'] = "Invalid status selected.";
        }

        return $errors;
    }

    public static function validateLogin($data) {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = "Email is required.";
        } elseif (!self::validateEmail($data['email'])) {
            $errors['email'] = "Please enter a valid email address.";
        }

        if (empty($data['password'])) {
            $errors['password'] = "Password is required.";
        } elseif (!self::validatePassword($data['password'])) {
            $errors['password'] = "Password must be at least 6 characters.";
        }

        return $errors;
    }

    public static function validateSignup($data) {
        $errors = self::validateLogin($data);

        if (empty($data['confirm_password'])) {
            $errors['confirm_password'] = "Please confirm your password.";
        } elseif ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = "Passwords do not match.";
        }

        return $errors;
    }
}