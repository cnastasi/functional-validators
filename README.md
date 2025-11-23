# Functional Validators

A PHP 8.5+ library providing functional validation using the pipe operator (`|>`), functors, and union types. Perfect for building Value Objects with elegant, composable validation.

## 🎯 Overview

This library provides a functional approach to Value Objects validation in PHP 8.5+. Instead of throwing exceptions on the first validation error, it accumulates all errors and returns them in a type-safe way using union types.

**Key Features:**
- ✅ **Functional Validation**: Uses PHP 8.5 pipes for elegant validation chains
- ✅ **Error Accumulation**: Collects all validation errors, not just the first one
- ✅ **Field-Level Errors**: For entities, errors are organized by field name
- ✅ **Type Safety**: Strong typing with union types (`ValueObject|ErrorsBag` or `Entity|MultipleFieldErrorsBag`)
- ✅ **Reusable Validators**: Library of composable validators

## 📚 Related Articles

This project is part of a blog series on Value Objects in PHP:

1. [Value Objects in PHP 8: Building a better code](https://dev.to/cnastasi/value-objects-in-php-8-building-a-better-code-38k8)
2. [Advanced Value Objects in PHP 8](https://dev.to/cnastasi/advanced-value-objects-in-php-8-1lp0)
3. [Value Object in PHP 8: Entities](https://dev.to/cnastasi/value-object-in-php-8-entities-1jce)
4. [Value Object in PHP 8: Build your own type system](https://dev.to/cnastasi/value-object-in-php-8-build-your-own-type-system-5970)
5. **[Value Objects in PHP 8: Let's introduce a functional approach](https://dev.to/cnastasi/value-objects-in-php-8-lets-introduce-a-functional-approach-3aan)** (this project)

## 📋 Requirements

- PHP 8.5 or higher
- Composer

## 🚀 Installation

```bash
composer require cnastasi/functional-validators
```

Or add it manually to your `composer.json`:

```json
{
    "require": {
        "cnastasi/functional-validators": "^0.1"
    }
}
```

## 💡 Usage

### Single Value Object

```php
use CN\FunctionalValidators\Examples\Age;
use CN\FunctionalValidators\Errors\ErrorsBag;

// Valid age
$age = Age::create(25);
if ($age instanceof Age) {
    echo $age->value; // 25
}

// Invalid age - returns ErrorsBag with all errors
$result = Age::create(-5);
if ($result instanceof ErrorsBag) {
    foreach ($result->getErrors() as $error) {
        echo $error->message . "\n";
        // Output: "Age cannot be negative"
    }
}
```

### Entity with Multiple Fields

```php
use CN\FunctionalValidators\Examples\Person;
use CN\FunctionalValidators\Errors\MultipleFieldErrorsBag;

$result = Person::create('', 'invalid-email', -5);
// Returns Person|MultipleFieldErrorsBag

if ($result instanceof MultipleFieldErrorsBag) {
    // Get errors organized by field
    foreach ($result->getErrorsByField() as $field => $errors) {
        echo "Field '{$field}':\n";
        foreach ($errors as $error) {
            echo "  - {$error->message}\n";
        }
    }
    
    // Or get errors for a specific field
    $nameErrors = $result->getErrorsForField('name');
    
    // Get all fields with errors
    $fieldsWithErrors = $result->getFieldsWithErrors();
}
```

### Password Value Object

```php
use CN\FunctionalValidators\Examples\Password;
use CN\FunctionalValidators\Errors\ErrorsBag;

$result = Password::create('weak');
// Returns Password|ErrorsBag

if ($result instanceof Password) {
    // Password is automatically encrypted
    $encrypted = $result->value;
    
    // Verify password
    if ($result->verify('weak')) {
        // Password matches
    }
} elseif ($result instanceof ErrorsBag) {
    // Shows all validation errors:
    // - Password must be at least 8 characters long
    // - Password must contain at least one uppercase letter
    // - Password must contain at least one number
    // - Password must contain at least one special character
    foreach ($result->getErrors() as $error) {
        echo $error->message . "\n";
    }
}
```

## 🔨 Building Your Own Value Objects

The examples in `CN\FunctionalValidators\Examples\` are just reference implementations. Here's how to build your own Value Objects using the library:

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

### String-Based Value Object Example

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

### Complex Validation Example

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

### Key Points

1. **Private Constructor**: Ensures all instantiation goes through `create()`
2. **Union Return Type**: Always return `YourValueObject|ErrorsBag` for type safety
3. **Validation Pipeline**: Use pipes (`|>`) to chain validators
4. **Error Accumulation**: All validation errors are collected automatically
5. **Custom Error Messages**: Provide meaningful error messages for each validator
6. **Type Safety**: The return type ensures you must handle both success and failure cases

## 🏗️ Architecture

### Namespace Structure

- **`CN\FunctionalValidators\Validators\`**: Core validation classes
  - `ValidationContext`: The functor that accumulates errors during validation
  - `MultipleValidationContext`: Handles validation of multiple fields for entities
  - `IntegerValue`, `StringValue`: Pipe-friendly validators
- **`CN\FunctionalValidators\Errors\`**: Error handling classes
  - `ErrorsBag`: Collection of errors for single value objects
  - `MultipleFieldErrorsBag`: Collection of errors organized by field name for entities
  - `Error`: Single error representation
- **`CN\FunctionalValidators\Examples\`**: Example Value Objects (Age, Email, Name, Password, Person)

### Core Components

- **`ValidationContext`**: The functor that accumulates errors during validation
- **`MultipleValidationContext`**: Handles validation of multiple fields for entities
- **`ErrorsBag`**: Collection of errors for single value objects
- **`MultipleFieldErrorsBag`**: Collection of errors organized by field name for entities
- **Validators**: Pipe-friendly validators (`StringValue`, `IntegerValue`, etc.)

### Available Validators

#### IntegerValue
- `from(mixed $value)`: Creates a validation context from a value
- `isInteger(?string $errorMessage)`: Validates the value is an integer
- `min(int $min, ?string $errorMessage)`: Validates minimum value
- `max(int $max, ?string $errorMessage)`: Validates maximum value
- `between(int $min, int $max, ?string $errorMessage)`: Validates value is within range

#### StringValue
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

## 🧪 Testing

This project uses [Pest](https://pestphp.com/) for testing.

```bash
# Install dependencies
composer install

# Run all tests
composer test
# or
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Models/AgeTest.php

# Run tests with coverage
./vendor/bin/pest --coverage

# Run tests in watch mode
./vendor/bin/pest --watch
```

## 📖 Documentation

For a detailed explanation of the concepts and design decisions, see the [blog article](BLOG_ARTICLE.md) that accompanies this project.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please make sure to:
- Follow the existing code style
- Add tests for new features
- Update documentation as needed
- Run tests before submitting

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 👤 Author

**Christian Nastasi**

- Blog: [dev.to/cnastasi](https://dev.to/cnastasi)
- Email: christian.nastasi@gmail.com

## 🙏 Acknowledgments

This project explores functional programming concepts in PHP, specifically:
- PHP 8.5 pipe operator (`|>`)
- Functors for error accumulation
- Union types instead of Either monads
- Reusable validator composition

## ⚠️ Note

This is an experimental project exploring PHP 8.5 features. While functional and tested, it's primarily intended as a learning resource and proof of concept.
