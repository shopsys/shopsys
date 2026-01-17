<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AccessCheckerExtension extends AbstractExtension
{
    public function __construct(
        private readonly AccessCheckerInterface $accessChecker,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('can_view', $this->accessChecker->canView(...)),
            new TwigFunction('can_edit', $this->accessChecker->canEdit(...)),
            new TwigFunction('can_delete', $this->accessChecker->canDelete(...)),
            new TwigFunction('can_create', $this->accessChecker->canCreate(...)),
            new TwigFunction('has_access_to_route', $this->accessChecker->hasAccessToRoute(...)),
        ];
    }
}
