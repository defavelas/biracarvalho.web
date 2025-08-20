<?php

declare(strict_types=1);

namespace App\Enum\Location;

enum Category: string
{
    case ACCESSIBLE = 'accessible';
    case PARTIALLY_ACCESSIBLE = 'partially_accessible';
    case NON_ACCESSIBLE = 'non_accessible';

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
     * Get the values for the type.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
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
            self::NON_ACCESSIBLE => __('Não Acessível'),
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
            self::NON_ACCESSIBLE => 'red',
        };
    }
}
