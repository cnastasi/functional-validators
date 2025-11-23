<?php

namespace ValueObjects\Errors;

interface Failable
{
    public function orFail(): static;
}