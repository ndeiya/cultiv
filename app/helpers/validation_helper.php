<?php
/**
 * Validation Helper
 * Common input validation and sanitization functions.
 */

/**
 * Validate that required fields are present and non-empty.
 *
 * @param array $fields  List of required field names
 * @param array $data    The data array to check against
 * @return array         List of missing/empty field names (empty if all valid)
 */
function validate_required(array $fields, array $data): array
{
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Validate an email address format.
 */
function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize a string input — trim whitespace and strip HTML tags.
 */
function sanitize_input(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize an array of inputs recursively.
 */
function sanitize_array(array $data): array
{
    $sanitized = [];
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $sanitized[$key] = sanitize_input($value);
        } elseif (is_array($value)) {
            $sanitized[$key] = sanitize_array($value);
        } else {
            $sanitized[$key] = $value;
        }
    }
    return $sanitized;
}

/**
 * Validate that a value is one of the allowed options.
 */
function validate_in(string $value, array $allowed): bool
{
    return in_array($value, $allowed, true);
}

/**
 * Validate a string meets minimum and maximum length requirements.
 */
function validate_length(string $value, int $min = 0, int $max = PHP_INT_MAX): bool
{
    $len = mb_strlen($value);
    return $len >= $min && $len <= $max;
}
