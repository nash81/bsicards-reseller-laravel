<?php
/**
 * PHPStan Bootstrap File
 * Provides stubs for Laravel functions so PHPStan doesn't complain
 */

if (!function_exists('config')) {
    /**
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    function config($key = null, $default = null) {
        return $default;
    }
}

if (!function_exists('config_path')) {
    /**
     * @param string $path
     * @return string
     */
    function config_path($path = '') {
        return $path;
    }
}

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

