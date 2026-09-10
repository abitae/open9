<?php

namespace App\Enums;

enum StorageDriver: string
{
    case Local = 'local';
    case Gcs = 'gcs';
}
