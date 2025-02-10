<?php

namespace App\Enum;

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case UNDEFINED = '';
    case OTHER = 'other';
}
