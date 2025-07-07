<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

final class SystemRole
{
    /**
     * Super admin role with full access to everything
     */
    public const string SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * Public access role for unauthenticated users
     */
    public const string PUBLIC_ACCESS = 'PUBLIC_ACCESS';

    /**
     * Admin role for administrative access
     */
    public const string ADMIN = 'ROLE_ADMIN';

    /**
     * All permissions role
     */
    public const string ALL = 'ROLE_ALL';

    /**
     * All view permissions role
     */
    public const string ALL_VIEW = 'ROLE_ALL_VIEW';

    /**
     * @return string[]
     */
    public static function getAll(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::ALL,
            self::ALL_VIEW,
            self::PUBLIC_ACCESS,
        ];
    }
}
