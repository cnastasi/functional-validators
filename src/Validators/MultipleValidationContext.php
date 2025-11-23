<?php

namespace ValueObjects\Validators;

use ValueObjects\Errors\ErrorsBag;
use ValueObjects\Errors\MultipleFieldErrorsBag;

/**
 * Handles validation of multiple value objects for entity creation
 * Collects all errors from multiple ValueObject|ErrorsBag results
 */
readonly class MultipleValidationContext
{
    /**
     * @param array<string, mixed> $values Validated values keyed by field name
     * @param MultipleFieldErrorsBag $errorsBag Errors organized by field name
     */
    private function __construct(
        private array $values,
        private MultipleFieldErrorsBag $errorsBag
    ) {
    }

    /**
     * Setup validation context from multiple ValueObject|ErrorsBag results
     * Each named argument should be the result of a ValueObject::create() call
     * 
     * @param mixed ...$results Named arguments: fieldName => (ValueObject|ErrorsBag)
     * @return static
     */
    public static function setup(...$results): static
    {
        $values = [];
        $errorsByField = [];

        foreach ($results as $fieldName => $result) {
            if ($result instanceof ErrorsBag) {
                // Store errors for this field
                $errorsByField[$fieldName] = $result->getErrors();
            } else {
                // Store validated value
                $values[$fieldName] = $result->value;
            }
        }

        $errorsBag = MultipleFieldErrorsBag::fromArray($errorsByField);

        return new static($values, $errorsBag);
    }

    /**
     * Check if all validations passed (no errors)
     */
    public function isValid(): bool
    {
        return $this->errorsBag->isEmpty();
    }

    /**
     * Get all validated values as an associative array
     * Returns array keyed by field names, ready to be spread into constructor
     * 
     * @return array<string, mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Get all accumulated errors organized by field
     */
    public function getErrors(): MultipleFieldErrorsBag
    {
        return $this->errorsBag;
    }
}