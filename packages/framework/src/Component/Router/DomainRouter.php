<?php

namespace Shopsys\FrameworkBundle\Component\Router;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Router\Exception\NotSupportedException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class DomainRouter extends ChainRouter
{
    protected bool $freeze = false;

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     * @param \Symfony\Component\Routing\RouterInterface $basicRouter
     * @param \Symfony\Component\Routing\RouterInterface $localizedRouter
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter $friendlyUrlRouter
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        RequestContext $context,
        RouterInterface $basicRouter,
        RouterInterface $localizedRouter,
        protected readonly FriendlyUrlRouter $friendlyUrlRouter,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($logger);

        $this->setContext($context);
        $this->freeze = true;

        $this->add($basicRouter, 30);
        $this->add($localizedRouter, 20);
        $this->add($friendlyUrlRouter, 10);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function generateByFriendlyUrl(FriendlyUrl $friendlyUrl, array $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->friendlyUrlRouter->generateByFriendlyUrl($friendlyUrl, $parameters, $referenceType);
    }

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     */
    public function setContext(RequestContext $context)
    {
        if ($this->freeze) {
            $message = 'Set context is not supported in chain DomainRouter';
            throw new NotSupportedException($message);
        }

        parent::setContext($context);
    }
}
