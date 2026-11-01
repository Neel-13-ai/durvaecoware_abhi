<?php
/**
 * Sanitization & Validation Helpers
 * Durva Eco Ware
 */

function clean_string(?string $str): string {
    return trim(strip_tags((string)($str ?? '')));
}

function clean_email(?string $email): string {
    return filter_var(trim((string)($email ?? '')), FILTER_SANITIZE_EMAIL);
}

function clean_phone(?string $phone): string {
    return preg_replace('/[^0-9+\-\s()]/', '', trim((string)($phone ?? '')));
}

function clean_int($val, int $default = 0): int {
    return filter_var($val, FILTER_VALIDATE_INT) !== false ? (int)$val : $default;
}

function clean_float($val, float $default = 0.0): float {
    return filter_var($val, FILTER_VALIDATE_FLOAT) !== false ? (float)$val : $default;
}

function slugify(string $text): string {
    // Replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // Lowercase
    $text = strtolower($text);

    return empty($text) ? 'item-' . time() : $text;
}
