<?php

namespace CN\FunctionalValidators\Examples;

use CN\FunctionalValidators\Errors\ErrorsBag;
use CN\FunctionalValidators\Errors\Failable;
use CN\FunctionalValidators\Errors\FailableSuccess;
use CN\FunctionalValidators\Validators\StringValue;

readonly final class Email implements Failable
{
    use FailableSuccess;

    private function __construct(public string $value)
    {
    }

    public static function create(mixed $value): Email|ErrorsBag
    {
        $context = Email::validate($value);

        return $context->isValid()
            ? new Email($context->getValue())
            : $context->getErrors();
    }

    public static function validate(mixed $value): StringValue
    {
        return $value
                |> StringValue::from(...)
                |> StringValue::email("Invalid email format");
    }
}