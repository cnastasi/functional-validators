<?php

use CN\FunctionalValidators\Examples\Age;
use CN\FunctionalValidators\Examples\Email;
use CN\FunctionalValidators\Examples\Name;
use CN\FunctionalValidators\Errors\MultipleFieldErrorsBag;
use CN\FunctionalValidators\Validators\MultipleValidationContext;

test('creates context with all valid values', function () {
    $context = MultipleValidationContext::setup(
        name: Name::create('John Doe'),
        email: Email::create('john@example.com'),
        age: Age::create(30)
    );
    
    expect($context->isValid())->toBeTrue()
        ->and($context->getValues())->toHaveKeys(['name', 'email', 'age'])
        ->and($context->getValues()['name'])->toBe('John Doe')
        ->and($context->getValues()['email'])->toBe('john@example.com')
        ->and($context->getValues()['age'])->toBe(30);
});

test('creates context with errors and collects them by field', function () {
    $context = MultipleValidationContext::setup(
        name: Name::create(''),
        email: Email::create('invalid-email'),
        age: Age::create(-5)
    );
    
    expect($context->isValid())->toBeFalse();
    
    $errors = $context->getErrors();
    expect($errors)->toBeInstanceOf(MultipleFieldErrorsBag::class)
        ->and($errors->hasErrors())->toBeTrue()
        ->and($errors->getFieldCount())->toBe(3)
        ->and($errors->getFieldsWithErrors())->toContain('name')
        ->and($errors->getFieldsWithErrors())->toContain('email')
        ->and($errors->getFieldsWithErrors())->toContain('age');
});

test('mixes valid and invalid values', function () {
    $context = MultipleValidationContext::setup(
        name: Name::create('John Doe'),
        email: Email::create('invalid-email'),
        age: Age::create(30)
    );
    
    expect($context->isValid())->toBeFalse()
        ->and($context->getValues())->toHaveKey('name')
        ->and($context->getValues())->toHaveKey('age')
        ->and($context->getValues())->not->toHaveKey('email'); // Email has errors, not in values
    
    $errors = $context->getErrors();
    expect($errors->getFieldsWithErrors())->toContain('email')
        ->and($errors->getFieldsWithErrors())->not->toContain('name')
        ->and($errors->getFieldsWithErrors())->not->toContain('age');
});

test('can get errors for specific field', function () {
    $context = MultipleValidationContext::setup(
        name: Name::create(''),
        email: Email::create('john@example.com'),
        age: Age::create(30)
    );
    
    $errors = $context->getErrors();
    $nameErrors = $errors->getErrorsForField('name');
    
    expect($nameErrors)->not->toBeEmpty()
        ->and($errors->getErrorsForField('email'))->toBeEmpty()
        ->and($errors->getErrorsForField('age'))->toBeEmpty();
});

