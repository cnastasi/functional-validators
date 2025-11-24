<?php

namespace CN\FunctionalValidators\Errors;

interface Failable
{
    public function orFail(): static;
}