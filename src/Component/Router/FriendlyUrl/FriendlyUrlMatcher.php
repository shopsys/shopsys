<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use App\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlMatcher as BaseFriendlyUrlMatcher;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Symfony\Component\Routing\RouteCollection;

class FriendlyUrlMatcher extends BaseFriendlyUrlMatcher
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        FriendlyUrlRepository $friendlyUrlRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($friendlyUrlRepository);

        $this->entityManager = $entityManager;
    }

    /**
     * @param string $pathinfo
     * @param \Symfony\Component\Routing\RouteCollection $routeCollection
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return array
     */
    public function match($pathinfo, RouteCollection $routeCollection, DomainConfig $domainConfig)
    {
        $pathWithoutSlash = substr($pathinfo, 1);
        $friendlyUrl = $this->friendlyUrlRepository->findByDomainIdAndSlug($domainConfig->getId(), $pathWithoutSlash);

        if ($friendlyUrl === null) {
            throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException();
        }

        $route = $routeCollection->get($friendlyUrl->getRouteName());
        if ($route === null) {
            throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException();
        }

        $matchedParameters = $route->getDefaults();

        if ($friendlyUrl->getRouteName() === 'front_category_seo') {
            $readyCategorySeoMixId = $friendlyUrl->getEntityId();
            $readyCategorySeoMix = $this->getReadyCategorySeoMixById($readyCategorySeoMixId);

            $matchedParameters['_route'] = 'front_product_list';
            $matchedParameters['id'] = $readyCategorySeoMix->getCategory()->getId();
            $matchedParameters['readyCategorySeoMixId'] = $readyCategorySeoMixId;
        } else {
            $matchedParameters['_route'] = $friendlyUrl->getRouteName();
            $matchedParameters['id'] = $friendlyUrl->getEntityId();
        }

        if (!$friendlyUrl->isMain()) {
            $matchedParameters['_controller'] = 'FrameworkBundle:Redirect:redirect';
            $matchedParameters['route'] = $friendlyUrl->getRouteName();
            $matchedParameters['permanent'] = true;
        }

        return $matchedParameters;
    }

    /**
     * Due to circular reference of ReadyCategorySeoMixFacade
     *
     * @param int $id
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function getReadyCategorySeoMixById(int $id): ReadyCategorySeoMix
    {
        $readyCategorySeoMix = $this->entityManager->getRepository(ReadyCategorySeoMix::class)->find($id);

        if ($readyCategorySeoMix === null) {
            throw new ReadyCategorySeoMixNotFoundException(sprintf('ReadyCategorySeoMix with ID %s not found', $id));
        }

        return $readyCategorySeoMix;
    }
}
