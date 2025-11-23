<?php

namespace ValueObjects\Errors;

trait FailableSuccess
{
    public function orFail(): static
    {
        return $this;
    }
}