<?php

namespace CN\FunctionalValidators\Examples;

use CN\FunctionalValidators\Errors\ErrorsBag;
use CN\FunctionalValidators\Errors\Failable;
use CN\FunctionalValidators\Errors\FailableSuccess;
use CN\FunctionalValidators\Validators\StringValue;

readonly final class Name implements Failable
{
    use FailableSuccess;

    private function __construct(public string $value)
    {
    }

    public static function create(mixed $value): Name|ErrorsBag
    {
        $context = Name::validate($value);

        return $context->isValid()
            ? new Name($context->getValue())
            : $context->getErrors();
    }

    public static function validate(mixed $value): StringValue
    {
        return $value
                |> StringValue::from(...)
                |> StringValue::minLength(2, "Name cannot be less than 2 characters")
                |> StringValue::maxLength(150, "Name cannot exceed 150 characters");
    }
}