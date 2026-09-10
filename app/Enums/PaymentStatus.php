<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
