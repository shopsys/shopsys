<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig;

use Override;
use Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItemsRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminDropdownMenuExtension extends AbstractExtension
{
    public function __construct(
        protected readonly AdminDropdownMenuItemsRenderer $adminDropdownMenuItemsRenderer,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'renderAdminDropdownMenuItems',
                $this->adminDropdownMenuItemsRenderer->render(...),
                ['is_safe' => ['html']],
            ),
        ];
    }
}
