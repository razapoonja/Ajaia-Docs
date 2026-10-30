<?php

namespace App\Enums;

enum ShareRole: string
{
    case Viewer = 'viewer';
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::Viewer => 'Can view',
            self::Editor => 'Can edit',
        };
    }
}
