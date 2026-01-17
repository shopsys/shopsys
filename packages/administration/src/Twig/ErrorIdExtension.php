<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ErrorIdExtension extends AbstractExtension
{
    public function __construct(
        private readonly ErrorIdProvider $errorIdProvider,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions()
    {
        return [
            new TwigFunction('error_id', $this->getErrorId(...)),
        ];
    }

    public function getErrorId(): string
    {
        return $this->errorIdProvider->getErrorId();
    }
}
