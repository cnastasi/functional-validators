<?php

require_once __DIR__ . '/vendor/autoload.php';

use CN\FunctionalValidators\Examples\Age;
use CN\FunctionalValidators\Examples\Password;
use CN\FunctionalValidators\Examples\Person;
use ValueObjects\Errors\ErrorsBag;
use ValueObjects\Errors\MultipleFieldErrorsBag;

echo "=== Functional Value Objects with PHP 8.5 Pipes & Context Pattern ===\n\n";

// Example 1: Single Value Object (functional approach - returns Age|ErrorsBag)
echo "1. Age Value Object (returns Age|ErrorsBag):\n";
$age1 = Age::create(25);
if ($age1 instanceof Age) {
    echo "   ✓ Created Age: {$age1->value}\n";
} elseif ($age1 instanceof ErrorsBag) {
    echo "   ✗ Errors: " . $age1 . "\n";
}

$invalidAge1 = Age::create(-5);
if ($invalidAge1 instanceof ErrorsBag) {
    echo "   ✗ Validation failed: " . $invalidAge1 . "\n";
} elseif ($invalidAge1 instanceof Age) {
    echo "   ✓ Created Age: {$invalidAge1->value}\n";
}

// Example 2: Single Value Object with union type (Age|ErrorsBag)
echo "\n2. Age Value Object (returns Age|ErrorsBag - can have multiple errors):\n";
$ageResult = Age::create(25);
if ($ageResult instanceof Age) {
    echo "   ✓ Valid age: {$ageResult->value}\n";
} elseif ($ageResult instanceof ErrorsBag) {
    echo "   ✗ Errors: " . $ageResult . "\n";
}

$invalidAgeResult = Age::create(-5);
if ($invalidAgeResult instanceof ErrorsBag) {
    echo "   ✗ Validation failed with " . $invalidAgeResult->count() . " error(s):\n";
    foreach ($invalidAgeResult->getErrors() as $error) {
        echo "      - {$error->message}\n";
    }
} elseif ($invalidAgeResult instanceof Age) {
    echo "   ✓ Created age: {$invalidAgeResult->value}\n";
}

// Example showing a value object can have multiple errors
echo "\n2b. Age with value that could fail multiple validations:\n";
$veryInvalidAge = Age::create(200); // Fails max validation
if ($veryInvalidAge instanceof ErrorsBag) {
    echo "   ✗ Age validation failed with " . $veryInvalidAge->count() . " error(s):\n";
    foreach ($veryInvalidAge->getErrors() as $error) {
        echo "      - {$error->message}\n";
    }
}

// Example 2c: Password Value Object with multiple validation rules
echo "\n2c. Password Value Object (multiple validation rules):\n";
$validPassword = Password::create("MyP@ssw0rd");
if ($validPassword instanceof Password) {
    echo "   ✓ Password created and encrypted successfully!\n";
    echo "      Encrypted hash: " . substr($validPassword->value, 0, 20) . "...\n";
    
    // Verify the password
    if ($validPassword->verify("MyP@ssw0rd")) {
        echo "      ✓ Password verification successful\n";
    }
} elseif ($validPassword instanceof ErrorsBag) {
    echo "   ✗ Password validation failed:\n";
    foreach ($validPassword->getErrors() as $error) {
        echo "      - {$error->message}\n";
    }
}

// Invalid password - missing multiple requirements
echo "\n2d. Invalid Password (missing multiple requirements):\n";
$invalidPassword = Password::create("weak");
if ($invalidPassword instanceof ErrorsBag) {
    echo "   ✗ Password validation failed with " . $invalidPassword->count() . " error(s):\n";
    foreach ($invalidPassword->getErrors() as $error) {
        echo "      - {$error->message}\n";
    }
    echo "\n   Note: Password accumulates ALL validation errors (length, uppercase, lowercase, number, special char)!\n";
}

// Example 3: Person Entity with MultipleValidationContext
echo "\n3. Person Entity using MultipleValidationContext:\n";
echo "   (Validates Name, Email, and Age - accumulates ALL errors)\n\n";

// Valid person - all validations pass
echo "3a. Valid Person (all fields valid):\n";
$personResult = Person::create("John Doe", "john@example.com", 30);
if ($personResult instanceof Person) {
    echo "   ✓ Created Person successfully!\n";
    echo "      Name: {$personResult->name}\n";
    echo "      Email: {$personResult->email}\n";
    echo "      Age: {$personResult->age}\n";
} elseif ($personResult instanceof MultipleFieldErrorsBag) {
    echo "   ✗ Validation failed: " . $personResult . "\n";
}

// Invalid person - single field error
echo "\n3b. Invalid Person (single field error - Age):\n";
$invalidPerson1 = Person::create("Jane Smith", "jane@example.com", -5);
if ($invalidPerson1 instanceof MultipleFieldErrorsBag) {
    echo "   ✗ Validation failed with " . $invalidPerson1->count() . " error(s):\n";
    $errorsByField = $invalidPerson1->getErrorsByField();
    foreach ($errorsByField as $field => $errors) {
        echo "      Field '{$field}':\n";
        foreach ($errors as $error) {
            echo "         - {$error->message}\n";
        }
    }
} elseif ($invalidPerson1 instanceof Person) {
    echo "   ✓ Created Person: {$invalidPerson1->name}\n";
}

// Invalid person - multiple field errors (accumulates ALL)
echo "\n3c. Invalid Person (multiple field errors - ALL accumulated):\n";
$invalidPerson2 = Person::create("", "invalid-email", 200);
if ($invalidPerson2 instanceof MultipleFieldErrorsBag) {
    echo "   ✗ Validation failed with " . $invalidPerson2->count() . " error(s) across " . $invalidPerson2->getFieldCount() . " field(s):\n";
    foreach ($invalidPerson2->getErrorsByField() as $field => $errors) {
        echo "      Field '{$field}':\n";
        foreach ($errors as $error) {
            echo "         - {$error->message}\n";
        }
    }
    echo "\n   Note: All errors from Name, Email, and Age are collected with field information!\n";
} elseif ($invalidPerson2 instanceof Person) {
    echo "   ✓ Created Person: {$invalidPerson2->name}\n";
}

// Invalid person - all fields invalid
echo "\n3d. Invalid Person (all fields invalid - maximum error accumulation):\n";
$invalidPerson3 = Person::create("A", "not-an-email", -10);
if ($invalidPerson3 instanceof MultipleFieldErrorsBag) {
    echo "   ✗ Validation failed with " . $invalidPerson3->count() . " error(s):\n";
    foreach ($invalidPerson3->getErrorsByField() as $field => $errors) {
        echo "      Field '{$field}':\n";
        foreach ($errors as $error) {
            echo "         - {$error->message}\n";
        }
    }
    echo "\n   Note: MultipleFieldErrorsBag organizes errors by field name!\n";
} elseif ($invalidPerson3 instanceof Person) {
    echo "   ✓ Created Person: {$invalidPerson3->name}\n";
}

// Example 4: Access errors for specific fields
echo "\n4. Person Entity - Access errors for specific fields:\n";
$invalidPerson4 = Person::create("", "bad-email", 200);
if ($invalidPerson4 instanceof MultipleFieldErrorsBag) {
    echo "   ✗ Validation failed - accessing specific fields:\n";
    
    // Get errors for specific field
    $nameErrors = $invalidPerson4->getErrorsForField('name');
    if (!empty($nameErrors)) {
        echo "      Name errors (" . count($nameErrors) . "):\n";
        foreach ($nameErrors as $error) {
            echo "         - {$error->message}\n";
        }
    }
    
    $emailErrors = $invalidPerson4->getErrorsForField('email');
    if (!empty($emailErrors)) {
        echo "      Email errors (" . count($emailErrors) . "):\n";
        foreach ($emailErrors as $error) {
            echo "         - {$error->message}\n";
        }
    }
    
    echo "\n   Fields with errors: " . implode(', ', $invalidPerson4->getFieldsWithErrors()) . "\n";
}

// Example 5: Single field with multiple errors
echo "\n5. Person Entity - Single field with multiple errors:\n";
echo "   (Demonstrating that a single field can accumulate multiple validation errors)\n\n";

// Note: In the current implementation, each field runs its validation chain
// and can accumulate multiple errors. For example, if Age validation had both
// isInteger() and min() checks, a non-integer value could fail both.
// Let's show a case where we can see the structure supports multiple errors per field.

$invalidPerson5 = Person::create("X", "test", 250);
if ($invalidPerson5 instanceof MultipleFieldErrorsBag) {
    echo "   ✗ Validation failed - errors organized by field:\n\n";
    
    foreach ($invalidPerson5->getErrorsByField() as $field => $errors) {
        $errorCount = count($errors);
        $plural = $errorCount > 1 ? 's' : '';
        echo "   Field '{$field}' has {$errorCount} error{$plural}:\n";
        foreach ($errors as $index => $error) {
            echo "      " . ($index + 1) . ". {$error->message}\n";
        }
        echo "\n";
    }
    
    echo "   Summary:\n";
    echo "      - Total errors: " . $invalidPerson5->count() . "\n";
    echo "      - Fields with errors: " . $invalidPerson5->getFieldCount() . "\n";
    echo "      - Field names: " . implode(', ', $invalidPerson5->getFieldsWithErrors()) . "\n";
    echo "\n   Note: The system supports multiple errors per field.\n";
    echo "   Each field's validation chain can produce multiple error messages.\n";
}

// Example 6: Show the elegance of pipes with Context Pattern
echo "\n5. Direct pipe usage (showing the elegance):\n";
$nameContext = "Alice"
    |> \ValueObjects\Validators\StringValue::from(...)
    |> \ValueObjects\Validators\StringValue::notEmpty("Name is required")
    |> \ValueObjects\Validators\StringValue::minLength(2, "Name too short");

if ($nameContext->isValid()) {
    echo "   ✓ Name validated: {$nameContext->getValue()}\n";
} else {
    echo "   ✗ Name errors: " . implode(', ', $nameContext->getErrors()) . "\n";
}

echo "\n=== Done ===\n";
