<?php

namespace CN\FunctionalValidators\Examples;

enum Currency: string
{
    case EUR = '€';
    case USD = '$';

    // ...other currencies
}