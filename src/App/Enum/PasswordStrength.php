<?php

namespace App\Enum;

enum PasswordStrength: string
{
    case BAD = 'bad';
    case GOOD = 'good';
    case PERFECT = 'perfect';
}
