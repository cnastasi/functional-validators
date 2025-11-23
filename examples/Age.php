<?php

namespace CN\FunctionalValidators\Examples;

use ValueObjects\Errors\ErrorsBag;
use ValueObjects\Errors\Failable;
use ValueObjects\Errors\FailableSuccess;
use ValueObjects\Validators\IntegerValue;

readonly final class Age implements Failable
{
    use FailableSuccess;
    private function __construct(public int $value)
    {
    }

    public static function create(mixed $value): Age|ErrorsBag
    {
        $context = self::validate($value);

        return $context->isValid()
            ? new self($context->getValue())
            : $context->getErrors();
    }

    public static function validate(int $value): IntegerValue
    {
        return $value
            |> IntegerValue::from(...)
            |> IntegerValue::min(0, "Age cannot be negative")
            |> IntegerValue::max(150, "Age cannot exceed 150");
    }
}
