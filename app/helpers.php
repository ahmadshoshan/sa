<?php

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        static $cache = [];
        
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        
        $value = \App\Models\Setting::where('key', $key)->value('value');
        $cache[$key] = $value ?? $default;
        
        return $cache[$key];
    }
}