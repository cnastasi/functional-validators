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
                |> StringValue::notEmpty("Money string cannot be empty")
                |> StringValue::trim()
                |> StringValue::regex('/^(\d+(?:\.\d{1,2})?)([€$])$/u', "Invalid money format. Expected format: '100.00€' or '100.00$'")
                |> ArrayValue::map(
                    fn(array $matches) => [
                        'amount' => $matches[1], // The decimal amount string
                        'currency' => $matches[2] // The currency symbol
                    ])
                |> ArrayValue::map(
                    fn(array $parsed) => [
                        'amount' => (int)round((float)$parsed['amount'] * 100), // Convert to cents
                        'currency' => match ($parsed['currency']) {
                            '€' => Currency::EUR,
                            '$' => Currency::USD,
                            default => null,
                        }
                    ])
                |> ArrayValue::validateArray(
                    fn(array $parsed) => $parsed['currency'] !== null,
                    "Unsupported currency symbol"
                )
                |> ArrayValue::map(
                    fn(array $parsed) => self::create($parsed['amount'], $parsed['currency'])
                );

        return $context->isValid()
            ? $context->getValue()
            : $context->getErrors();
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