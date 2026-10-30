<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ShareRole: string implements HasColor, HasIcon, HasLabel
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

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Viewer => 'gray',
            self::Editor => 'primary',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::Viewer => Heroicon::OutlinedEye,
            self::Editor => Heroicon::OutlinedPencilSquare,
        };
    }
}
