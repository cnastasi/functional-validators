<?php

namespace CN\FunctionalValidators\Errors;

/**
 * Simple error data structure containing only a message
 */
readonly final class Error
{
    public function __construct(
        public string $message
    ) {
    }

    public function __toString(): string
    {
        return $this->message;
    }
}

