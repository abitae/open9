<?php

namespace App\Enums;

enum NewsletterStatus: string
{
    case Active = 'active';
    case Unsubscribed = 'unsubscribed';
}
