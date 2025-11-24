<?php

namespace CN\FunctionalValidators\Validators;

use CN\FunctionalValidators\Errors\Error;
use CN\FunctionalValidators\Errors\ErrorsBag;

/**
 * Validation Context Pattern
 * 
 * Holds a value being validated and accumulates errors as validations run.
 * Can be passed through PHP 8.5 pipes for elegant functional validation.
 */
readonly class ValidationContext
{
    /**
     * @param mixed $value The value being validated
     * @param array<string> $errors Accumulated error messages
     */
    private function __construct(
        private mixed $value,
        private array $errors
    ) {
    }

    /**
     * Create a new validation context for a value
     */
    protected static function of(mixed $value): self
    {
        return new static($value, []);
    }

    /**
     * Get the value being validated
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Get all accumulated errors as ErrorsBag
     */
    public function getErrors(): ErrorsBag
    {
        if (empty($this->errors)) {
            return ErrorsBag::empty();
        }
        
        $errorObjects = array_map(
            fn($message) => new Error($message),
            $this->errors
        );
        
        return new ErrorsBag($errorObjects);
    }

    /**
     * Check if validation has any errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation passed (no errors)
     */
    public function isValid(): bool
    {
        return !$this->hasErrors();
    }

    /**
     * Add an error to the context
     */
    public function addError(string $error): self
    {
        return new static($this->value, [...$this->errors, $error]);
    }

    /**
     * Validate using a predicate function
     * If validation fails, adds the error message to the context
     * Returns the context (for pipe chaining)
     */
    public function validate(callable $predicate, string $errorMessage): self
    {
        $isValid = $predicate($this->value);
        
        if (!$isValid) {
            return $this->addError($errorMessage);
        }
        
        return $this;
    }

    /**
     * Map the value if validation is still valid
     * Useful for transformations during validation
     */
    public function map(callable $mapper): self
    {
        if ($this->hasErrors()) {
            return $this;
        }
        
        return new self($mapper($this->value), $this->errors);
    }

    /**
     * Promise-like "then" method: if validation passed, execute callback with the value
     * If validation failed, return ErrorsBag immediately
     * This allows elegant chaining: context->then(fn($value) => new ValueObject($value))
     * 
     * @param callable $callback Function that receives the validated value and returns the result
     * @return ErrorsBag|mixed ErrorsBag if validation failed, callback result if valid
     */
    public function then(callable $callback)
    {
        return $this->hasErrors() 
            ? $this->getErrors() 
            : $callback($this->getValue());
    }

    /**
     * Combine multiple contexts into one
     * Merges all errors and values into arrays
     * Useful for entity validation
     */
    public static function combine(string $fieldName, self ...$contexts): self
    {
        $allErrorMessages = [];
        $values = [];
        
        foreach ($contexts as $context) {
            if ($context->hasErrors()) {
                // Extract error messages from ErrorsBag
                $allErrorMessages = array_merge($allErrorMessages, $context->getErrors()->getMessages());
            } else {
                $values[$fieldName] = $context->getValue();
            }
        }
        
        return new self($values, $allErrorMessages);
    }

    /**
     * Merge errors from multiple contexts into an ErrorsBag
     * Useful when validating entity properties separately
     */
    public static function mergeErrors(self ...$contexts): ErrorsBag
    {
        $errorsBag = ErrorsBag::empty();

        foreach ($contexts as $context) {
            if ($context->hasErrors()) {
                $errorsBag = $errorsBag->addAll($context->getErrors());
            }
        }
        return $errorsBag;
    }

    /**
     * Extract validated values from multiple contexts
     * Returns array of field => value for valid contexts
     */
    public static function extractValues(array $fieldContexts): array
    {
        $values = [];
        foreach ($fieldContexts as $field => $context) {
            if ($context instanceof self && $context->isValid()) {
                $values[$field] = $context->getValue();
            }
        }
        return $values;
    }
}

