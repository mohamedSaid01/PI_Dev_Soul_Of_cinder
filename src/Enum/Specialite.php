<?php

namespace App\Enum;

enum Specialite: string
{
    case CARDIOLOGIE = 'Cardiologie';
    case DERMATOLOGIE = 'Dermatologie';
    case PEDIATRIE = 'Pédiatrie';
    case NEUROLOGIE = 'Neurologie';
    case ORTHOPEDIE = 'Orthopédie';
    case AUTRE = 'Autre';
}