# Usage Guide

This guide explains how to build your own Value Objects using the Functional Validators library.

## Building Your Own Value Objects

The examples in `CN\FunctionalValidators\Examples\` are reference implementations. Here's how to build your own Value Objects:

### Step-by-Step Guide

#### 1. Create Your Value Object Class

Start with a `readonly final` class with a private constructor:

```php
readonly final class Price
{
    private function __construct(public int $value) {} // Price in cents
}
```

#### 2. Add the Factory Method

Create a static `create()` method that returns your Value Object or an `ErrorsBag`:

```php
use CN\FunctionalValidators\Validators\IntegerValue;
use CN\FunctionalValidators\Errors\ErrorsBag;

readonly final class Price
{
    private function __construct(public int $value) {} // Price in cents

    public static function create(mixed $value): Price|ErrorsBag
    {
        // Validation logic goes here
    }
}
```

#### 3. Build the Validation Pipeline

Use the pipe operator to chain validators from the library:

```php
public static function create(mixed $value): Price|ErrorsBag
{
    $context = $value
        |> IntegerValue::from(...)  // Start validation pipeline
        |> IntegerValue::min(0, "Price cannot be negative")
        |> IntegerValue::max(100000, "Price cannot exceed 1000.00€");

    return $context->isValid()
        ? new self($context->getValue())
        : $context->getErrors();
}
```

#### 4. Complete Example

Here's a complete example for a `Price` Value Object:

```php
use CN\FunctionalValidators\Validators\IntegerValue;
use CN\FunctionalValidators\Errors\ErrorsBag;

readonly final class Price
{
    private function __construct(public int $value) {} // Price in cents

    public static function create(mixed $value): Price|ErrorsBag
    {
        $context = $value
            |> IntegerValue::from(...)
            |> IntegerValue::min(0, "Price cannot be negative")
            |> IntegerValue::max(100000, "Price cannot exceed 1000.00€");

        return $context->isValid()
            ? new self($context->getValue())
            : $context->getErrors();
    }
}
```

## Examples

### String-Based Value Object

For string validation, use `StringValue` validators:

```php
use CN\FunctionalValidators\Validators\StringValue;
use CN\FunctionalValidators\Errors\ErrorsBag;

readonly final class Username
{
    private function __construct(public string $value) {}

    public static function create(mixed $value): Username|ErrorsBag
    {
        $context = $value
            |> StringValue::from(...)
            |> StringValue::minLength(3, "Username must be at least 3 characters")
            |> StringValue::maxLength(20, "Username cannot exceed 20 characters");

        return $context->isValid()
            ? new self($context->getValue())
            : $context->getErrors();
    }
}
```

### Complex Validation

You can combine multiple validators for complex rules:

```php
use CN\FunctionalValidators\Validators\StringValue;
use CN\FunctionalValidators\Errors\ErrorsBag;

readonly final class ProductCode
{
    private function __construct(public string $value) {}

    public static function create(mixed $value): ProductCode|ErrorsBag
    {
        $context = $value
            |> StringValue::from(...)
            |> StringValue::minLength(5, "Product code must be at least 5 characters")
            |> StringValue::maxLength(10, "Product code cannot exceed 10 characters")
            |> StringValue::hasUppercase("Product code must contain at least one uppercase letter")
            |> StringValue::hasNumber("Product code must contain at least one number");

        return $context->isValid()
            ? new self($context->getValue())
            : $context->getErrors();
    }
}
```

### Entity with Multiple Fields

For entities with multiple properties, use `MultipleValidationContext`:

```php
use CN\FunctionalValidators\Validators\MultipleValidationContext;
use CN\FunctionalValidators\Errors\MultipleFieldErrorsBag;

readonly final class Person
{
    private function __construct(
        public Name $name,
        public Email $email,
        public Age $age
    ) {}

    public static function create(string $name, string $email, int $age): Person|MultipleFieldErrorsBag
    {
        $context = MultipleValidationContext::setup(
            name: Name::validate($name),
            email: Email::validate($email),
            age: Age::validate($age)
        );

        return $context->isValid()
            ? new self(...$context->getValues())
            : $context->getErrors();
    }
}
```

## Key Points

1. **Private Constructor**: Ensures all instantiation goes through `create()`
2. **Union Return Type**: Always return `YourValueObject|ErrorsBag` for type safety
3. **Validation Pipeline**: Use pipes (`|>`) to chain validators
4. **Error Accumulation**: All validation errors are collected automatically
5. **Custom Error Messages**: Provide meaningful error messages for each validator
6. **Type Safety**: The return type ensures you must handle both success and failure cases

## Available Validators

### IntegerValue

- `from(mixed $value)`: Creates a validation context from a value
- `isInteger(?string $errorMessage)`: Validates the value is an integer
- `min(int $min, ?string $errorMessage)`: Validates minimum value
- `max(int $max, ?string $errorMessage)`: Validates maximum value
- `between(int $min, int $max, ?string $errorMessage)`: Validates value is within range

### StringValue

- `from(mixed $value)`: Creates a validation context from a value
- `isString(?string $errorMessage)`: Validates the value is a string
- `notEmpty(?string $errorMessage)`: Validates string is not empty
- `minLength(int $min, ?string $errorMessage)`: Validates minimum length
- `maxLength(int $max, ?string $errorMessage)`: Validates maximum length
- `email(?string $errorMessage)`: Validates email format
- `hasUppercase(?string $errorMessage)`: Validates contains uppercase letter
- `hasLowercase(?string $errorMessage)`: Validates contains lowercase letter
- `hasNumber(?string $errorMessage)`: Validates contains number
- `hasSpecialCharacter(?string $errorMessage)`: Validates contains special character

