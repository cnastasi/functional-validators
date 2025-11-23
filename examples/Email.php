<?php

namespace CN\FunctionalValidators\Examples;

use ValueObjects\Errors\ErrorsBag;
use ValueObjects\Errors\Failable;
use ValueObjects\Errors\FailableSuccess;
use ValueObjects\Validators\StringValue;

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