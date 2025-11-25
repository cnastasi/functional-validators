<?php

namespace CN\FunctionalValidators\Validators;

readonly class StringValue extends ValidationContext {
    public static function from(mixed $value): ValidationContext
    {
        return $value
                |> StringValue::of(...)
                |> StringValue::isString();
    }

    public static function isString(?string $errorMessage = null): \Closure
    {
        return static function (ValidationContext $context) use ($errorMessage) {
            $message = $errorMessage ?? "Value must be a string";

            $newContext = $context->validate(
                fn(mixed $value) => is_string($value),
                $message
            );

            return ($newContext->isValid())
                ? StringValue::of((string)($context->getValue()))
                : $context;
        };
    }
    public static function notEmpty(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "String cannot be empty";

        return static fn(ValidationContext $context) => $context->validate(
            fn(string $value) => !empty($value),
            $message
        );
    }

    public static function minLength(int $min, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "String must be at least {$min} characters";

        return static fn(ValidationContext $context) => $context->validate(
            fn(string $value) => strlen($value) >= $min,
            $message
        );
    }

    public static function maxLength(int $max, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "String must be at most {$max} characters";

        return static fn(ValidationContext $context) => $context->validate(
            fn(string $value) => strlen($value) <= $max,
            $message
        );
    }

    public static function email(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Invalid email format";

        return static fn(ValidationContext $context) => $context->validate(
            fn(string $value) => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            $message
        );
    }

    /**
     * Validate that string contains at least one uppercase letter
     */
    public static function hasUppercase(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Must contain at least one uppercase letter";

        return static fn(ValidationContext $context) => $context->validate(
            fn(string $value) => preg_match('/[A-Z]/', $value) === 1,
            $message
        );
    }

    /**
     * Validate that string contains at least one lowercase letter
     */
    public static function hasLowercase(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Must contain at least one lowercase letter";

        return static fn(ValidationContext $context) => $context->validate(
            fn(string $value) => preg_match('/[a-z]/', $value) === 1,
            $message
        );
    }

    /**
     * Validate that string contains at least one digit
     */
    public static function hasNumber(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Must contain at least one number";

        return static fn(ValidationContext $context) => $context->validate(
            fn(string $value) => preg_match('/[0-9]/', $value) === 1,
            $message
        );
    }

    /**
     * Validate that string contains at least one special character
     */
    public static function hasSpecialCharacter(?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Must contain at least one special character";

        return static fn(ValidationContext $context) => $context->validate(
            fn(string $value) => preg_match('/[^a-zA-Z0-9]/', $value) === 1,
            $message
        );
    }

    /**
     * Trim whitespace from the string
     */
    public static function trim(): \Closure
    {
        return static fn(ValidationContext $context) => $context->applyMap(
            fn(string $value) => trim($value)
        );
    }

    /**
     * Validate string matches a regex pattern and extract matches
     * If valid, transforms the value to an array containing the full match and captured groups
     * 
     * @param string $pattern Regex pattern (should include capturing groups)
     * @param ?string $errorMessage Custom error message if pattern doesn't match
     * @return \Closure Validation closure
     */
    public static function regex(string $pattern, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "String does not match required pattern";

        return static function (ValidationContext $context) use ($pattern, $message) {
            $value = $context->getValue();
            
            if (!is_string($value)) {
                return $context->addError($message);
            }

            $matches = [];
            $isValid = preg_match($pattern, $value, $matches) === 1;

            if (!$isValid) {
                return $context->addError($message);
            }

            // Transform value to matches array (full match + captured groups)
            return $context->applyMap(fn() => $matches);
        };
    }
}
