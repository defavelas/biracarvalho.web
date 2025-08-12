<?php

declare(strict_types=1);

namespace App\Enums;

enum LocationType: string
{
    case ACCESSIBLE = 'accessible';
    case PARTIALLY_ACCESSIBLE = 'partially_accessible';
    case NOT_ACCESSIBLE = 'not_accessible';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $type) => [$type->value => $type->label()])
            ->toArray();
    }

    public function label(): string
    {
        return match ($this) {
            self::ACCESSIBLE => __('Acessível'),
            self::PARTIALLY_ACCESSIBLE => __('Parcialmente Acessível'),
            self::NOT_ACCESSIBLE => __('Não Acessível'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACCESSIBLE => 'green',
            self::PARTIALLY_ACCESSIBLE => 'yellow',
            self::NOT_ACCESSIBLE => 'red',
        };
    }
}
