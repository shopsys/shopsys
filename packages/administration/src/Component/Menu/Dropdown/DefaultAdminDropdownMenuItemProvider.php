<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Menu\Dropdown;

use Override;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DefaultAdminDropdownMenuItemProvider implements AdminDropdownMenuItemProviderInterface
{
    public function __construct(
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getItems(): array
    {
        return [
            new AdminDropdownMenuItem(
                t('My account'),
                'user-settings',
                $this->urlGenerator->generate('admin_administrator_myaccount'),
                1000,
            ),
            new AdminDropdownMenuItem(
                t('Log out'),
                'logout',
                $this->urlGenerator->generate('admin_logout', [
                    '_csrf_token' => $this->csrfTokenManager->getToken('admin_logout')->getValue(),
                ]),
                -1000,
                true,
            ),
        ];
    }
}
