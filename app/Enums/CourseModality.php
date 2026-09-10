<?php

namespace App\Enums;

enum CourseModality: string
{
    case Presencial = 'presencial';
    case Virtual = 'virtual';
    case Hibrido = 'hibrido';
    case Grabado = 'grabado';
}
