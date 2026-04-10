<?php
// Simple helper: set a flash message cookie and redirect
if (!function_exists('redirect_with_message')) {
    function redirect_with_message(bool $success, string $message, string $location): void
    {
        setcookie($success ? 'success' : 'error', $message, time() + 5, '/');
        header('Location: ' . $location);
        exit;
    }
}
