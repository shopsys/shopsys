<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Security;

use Override;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\RoleSection;

class FrontendApiRoleSectionProvider extends AbstractRoleSectionProvider
{
    public const string ALL = 'all';
    public const string INDIVIDUAL = 'individual';

    #[Override]
    protected function defineSections(): void
    {
        $this->addSection(new RoleSection(self::ALL, t('All roles'), 10, 'menu'));
        $this->addSection(new RoleSection(self::INDIVIDUAL, t('Individual roles'), 20, 'puzzle'));
    }

    /**
     * @{inheritDoc}
     */
    #[Override]
    public static function getTargetContext(): string
    {
        return FrontendApiContext::class;
    }
}
