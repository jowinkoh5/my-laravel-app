<?php

namespace App\Enums;

/**
 * Publication status of a product, backed by the `products.enabled` column.
 *
 * The database stores a boolean; this enum gives the admin and API filters a
 * validated, self-documenting vocabulary instead of a bare "0"/"1" parameter.
 */
enum ProductStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::Enabled => 'Enabled',
            self::Disabled => 'Disabled',
        };
    }

    public function toBoolean(): bool
    {
        return $this === self::Enabled;
    }

    public static function fromBoolean(bool $enabled): self
    {
        return $enabled ? self::Enabled : self::Disabled;
    }
}
