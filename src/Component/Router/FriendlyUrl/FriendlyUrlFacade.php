<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade as BaseFriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactoryInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory;

class FriendlyUrlFacade extends BaseFriendlyUrlFacade
{
    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlCacheFacade
     */
    private $friendlyUrlCacheFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFactory $friendlyUrlFactory
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlCacheFacade $friendlyUrlCacheFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        DomainRouterFactory $domainRouterFactory,
        FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory,
        FriendlyUrlRepository $friendlyUrlRepository,
        Domain $domain,
        FriendlyUrlFactoryInterface $friendlyUrlFactory,
        FriendlyUrlCacheFacade $friendlyUrlCacheFacade
    ) {
        parent::__construct(
            $em,
            $domainRouterFactory,
            $friendlyUrlUniqueResultFactory,
            $friendlyUrlRepository,
            $domain,
            $friendlyUrlFactory
        );

        $this->friendlyUrlCacheFacade = $friendlyUrlCacheFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $mainFriendlyUrl
     */
    protected function setFriendlyUrlAsMain(FriendlyUrl $mainFriendlyUrl)
    {
        $friendlyUrls = $this->friendlyUrlRepository->getAllByRouteNameAndEntityIdAndDomainId(
            $mainFriendlyUrl->getRouteName(),
            $mainFriendlyUrl->getEntityId(),
            $mainFriendlyUrl->getDomainId()
        );
        foreach ($friendlyUrls as $friendlyUrl) {
            $friendlyUrl->setMain(false);
        }
        $mainFriendlyUrl->setMain(true);

        $this->em->flush();

        $this->friendlyUrlCacheFacade->saveToCache($mainFriendlyUrl);
    }
}
