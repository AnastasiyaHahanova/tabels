<?php

namespace App\Controller\Api\v1;

class Validator
{

    public static function validate(array $data, array $requiredParameters = []): array
    {
        $errors = [];
        foreach ($requiredParameters as $parameter) {
            if (!isset($data[$parameter])) {
                $errors[] = sprintf('Parameter %s is required', $parameter);
            }
        }

        if ($errors) {
            return $errors;
        }

        foreach ($data as $parameter => $value) {
            if ($parameter === 'name') {
                if (strlen((string)$value) > 255) {
                    $errors[$parameter][] = 'Name is too long';
                }

                if (!preg_match("/^[A-Za-z0-9]+$/ui", $value)) {
                    $errors[$parameter][] = 'Username must contain only letters and numbers';
                }
            }

            if ($parameter === 'password') {
                if (strlen((string)$value) < 8) {
                    $errors[$parameter][] = 'Password must contain at least 8 characters';
                }
            }

            if ($parameter === 'email') {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$parameter][] = 'Wrong email format';
                }
            }

            if ($parameter === 'columns') {
                if (!is_array($value)) {
                    $errors[$parameter][] = sprintf('Wrong columns format. Expected array got %s', gettype($value));
                }
            }
        }

        return $errors;
    }
}