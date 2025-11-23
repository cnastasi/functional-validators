<?php

namespace ValueObjects\Validators;

readonly class IntegerValue extends ValidationContext
{
    public static function from(mixed $value): ValidationContext
    {
        return $value
                |> IntegerValue::of(...)
                |> IntegerValue::isInteger();
    }

    public static function isInteger(?string $errorMessage = null): \Closure
    {
        return static function (ValidationContext $context) use ($errorMessage) {
            $message = $errorMessage ?? "Value must be an integer";

            $newContext = $context->validate(
                fn(mixed $value) => is_int($value),
                $message
            );

            return ($newContext->isValid())
                ? IntegerValue::of(intval($context->getValue()))
                : $context;
        };
    }

    public static function min(int $min, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Value must be at least {$min}";

        return static fn(ValidationContext $context) => $context->validate(
            fn(int $value) => $value >= $min,
            $message
        );
    }

    public static function max(int $max, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Value must be at most {$max}";
        return static fn(ValidationContext $context) => $context->validate(
            fn(int $value) => $value <= $max,
            $message
        );
    }

    /**
     * Validate integer is within a range
     */
    public static function between(int $min, int $max, ?string $errorMessage = null): \Closure
    {
        $message = $errorMessage ?? "Value must be between {$min} and {$max}";

        return static fn(ValidationContext $context) => $context->validate(
            fn($value) => $value >= $min && $value <= $max,
            $message
        );
    }
}
