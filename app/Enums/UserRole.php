<?php

namespace App\Enums;

/**
 * Roles a user account may hold.
 *
 * Only an administrator may reach the admin dashboard or the write endpoints
 * of the product API; customers are limited to the public catalogue.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Customer => 'Customer',
        };
    }
}
