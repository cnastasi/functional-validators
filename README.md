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

## 💡 Quick Start

### Single Value Object

```php
use CN\FunctionalValidators\Examples\Age;
use CN\FunctionalValidators\Errors\ErrorsBag;

$result = Age::create(25);
if ($result instanceof Age) {
    echo $result->value; // 25
} elseif ($result instanceof ErrorsBag) {
    foreach ($result->getErrors() as $error) {
        echo $error->message . "\n";
    }
}
```

### Entity with Multiple Fields

```php
use CN\FunctionalValidators\Examples\Person;
use CN\FunctionalValidators\Errors\MultipleFieldErrorsBag;

$result = Person::create('', 'invalid-email', -5);
if ($result instanceof MultipleFieldErrorsBag) {
    foreach ($result->getErrorsByField() as $field => $errors) {
        echo "Field '{$field}':\n";
        foreach ($errors as $error) {
            echo "  - {$error->message}\n";
        }
    }
}
```

### Building Your Own Value Objects

```php
use CN\FunctionalValidators\Validators\IntegerValue;
use CN\FunctionalValidators\Errors\ErrorsBag;

readonly final class Price
{
    private function __construct(public int $value) {}

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

📖 **For detailed usage instructions, see [USAGE.md](USAGE.md)**

## 🏗️ Architecture

- **`CN\FunctionalValidators\Validators\`**: Core validation classes (`ValidationContext`, `MultipleValidationContext`, `IntegerValue`, `StringValue`)
- **`CN\FunctionalValidators\Errors\`**: Error handling (`ErrorsBag`, `MultipleFieldErrorsBag`, `Error`)
- **`CN\FunctionalValidators\Examples\`**: Example Value Objects (Age, Email, Name, Password, Person)

## 🧪 Testing

```bash
composer test
```

## 📖 Documentation

- **[Usage Guide](USAGE.md)**: Detailed guide on building your own Value Objects
- **[Blog Article](https://dev.to/cnastasi/value-objects-in-php-8-lets-introduce-a-functional-approach-3aan)**: Deep dive into the concepts and design decisions

## 🤝 Contributing

Contributions are welcome! See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

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
