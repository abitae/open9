<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Yape = 'yape';
    case Plin = 'plin';
    case Transferencia = 'transferencia';
    case Tarjeta = 'tarjeta';
    case Efectivo = 'efectivo';
    case Otro = 'otro';
}
