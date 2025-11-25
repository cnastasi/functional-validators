<?php

namespace CN\FunctionalValidators\Examples;

use CN\FunctionalValidators\Errors\ErrorsBag;
use CN\FunctionalValidators\Errors\Failable;
use CN\FunctionalValidators\Errors\FailableSuccess;
use CN\FunctionalValidators\Validators\ArrayValue;
use CN\FunctionalValidators\Validators\IntegerValue;
use CN\FunctionalValidators\Validators\StringValue;
use CN\FunctionalValidators\Validators\ValidationContext;

readonly final class Money implements Failable
{
    use FailableSuccess;

    private function __construct(
        public int      $amount,
        public Currency $currency
    )
    {
    }

    public static function fromString(string $value): Money|ErrorsBag
    {
        /** @var ValidationContext $context */
        $context = $value
                |> StringValue::from(...)
                // Check if is empty
                |> StringValue::notEmpty("Money string cannot be empty")
                // Strip leading/trailing whitespace
                |> StringValue::trim()
                // Extract amount and currency from the string
                |> StringValue::regex('/^(\d+(?:\.\d{1,2})?)([€$])$/u', "Invalid money format. Expected format: '100.00€' or '100.00$'")
                // Extract amount and currency from the regex match
                |> ArrayValue::mapKeys([1 => 'amount', 2 => 'currency'])
                |> ArrayValue::map(
                    fn(array $toParse) => [
                        // Parse amount to int and convert in cents
                        'amount' => Money::parseAmount($toParse['amount']),
                        // Parse currency to Currency Enum
                        'currency' => Money::parseCurrency($toParse['currency']),
                    ])
                // Validate that currency is supported (very rare case)
                |> ArrayValue::validateArray(
                    fn(array $parsed) => $parsed['currency'] !== null,
                    "Unsupported currency symbol"
                )
                // Create the Money object
                |> ArrayValue::map(
                    fn(array $parsed) => self::create($parsed['amount'], $parsed['currency'])
                );

        return $context->isValid()
            ? $context->getValue()
            : $context->getErrors();
    }

    private static function parseAmount(string $amount): int
    {
        return (int)round((float)$amount * 100); // Convert to cents
    }

    private static function parseCurrency(string $currency): Currency|null
    {
        return match ($currency) {
            '€' => Currency::EUR,
            '$' => Currency::USD,
            default => null,
        };
    }

    public static function create(mixed $amount, Currency $currency): Money|ErrorsBag
    {
        $context = self::validate($amount);

        return $context->isValid()
            ? new self($context->getValue(), $currency)
            : $context->getErrors();
    }

    public static function validate(mixed $amount): IntegerValue
    {
        return $amount
                |> IntegerValue::from(...)
                |> IntegerValue::min(0, "Amount cannot be negative");
    }

    /**
     * Convert Money to string representation (e.g., "100.00€")
     */
    public function __toString(): string
    {
        // Convert cents to decimal (divide by 100)
        $decimalAmount = $this->amount / 100;

        // Format with 2 decimal places (standard for currency)
        $formatted = number_format($decimalAmount, 2, '.', '');

        return $formatted . $this->currency->value;
    }
}