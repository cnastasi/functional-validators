<?php

namespace CN\FunctionalValidators\Errors;

trait FailableSuccess
{
    public function orFail(): static
    {
        return $this;
    }
}