<?php

namespace App\Enums;

enum SettingType: string
{
    case String = 'string';
    case Text = 'text';
    case Boolean = 'boolean';
    case Image = 'image';
    case Json = 'json';
    case Number = 'number';
}
