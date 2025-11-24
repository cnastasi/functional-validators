<?php

namespace CN\FunctionalValidators\Examples;

use CN\FunctionalValidators\Errors\MultipleFieldErrorsBag;
use CN\FunctionalValidators\Validators\MultipleValidationContext;

readonly final class Person
{
    public function __construct(
        public string $name,
        public string $email,
        public int    $age
    )
    {
    }

    public static function create(
        string $name,
        string $email,
        int    $age
    ): Person|MultipleFieldErrorsBag
    {
        $context = MultipleValidationContext::setup(
            name: Name::create($name),
            email: Email::create($email),
            age: Age::create($age)
        );

        return $context->isValid()
            ? new Person(...$context->getValues())
            : $context->getErrors();
    }
}
