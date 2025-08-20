<?php

declare(strict_types=1);

namespace App\Enum\Location;

enum Category: string
{
    case ACCESSIBLE = 'accessible';
    case PARTIALLY_ACCESSIBLE = 'partially_accessible';
    case NOT_ACCESSIBLE = 'not_accessible';

    /**
     * Get the options for the type.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $type) => [$type->value => $type->label()])
            ->toArray();
    }

    /**
     * Get the label for the type.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::ACCESSIBLE => __('Acessível'),
            self::PARTIALLY_ACCESSIBLE => __('Parcialmente Acessível'),
            self::NOT_ACCESSIBLE => __('Não Acessível'),
        };
    }

    /**
     * Get the color for the type.
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::ACCESSIBLE => 'green',
            self::PARTIALLY_ACCESSIBLE => 'yellow',
            self::NOT_ACCESSIBLE => 'red',
        };
    }

    /**
     * Get the values for the type.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
