<?php

declare(strict_types=1);

namespace App\Component\Grid\Ordering;

use App\Component\Domain\Domain;
use App\Model\Slider\SliderItem;
use App\Twig\Cache\TwigCacheFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingFacade as BaseGridOrderingFacade;

class GridOrderingFacade extends BaseGridOrderingFacade
{
    private const ENTITY_TO_CLEAR_CACHE_CONST_PAIRING = [
        SliderItem::class => TwigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE,
    ];

    /**
     * @var \App\Twig\Cache\TwigCacheFacade
     */
    private $twigCacheFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Twig\Cache\TwigCacheFacade $twigCacheFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        EntityManagerInterface $em,
        TwigCacheFacade $twigCacheFacade,
        Domain $domain,
        EntityNameResolver $entityNameResolver
    ) {
        parent::__construct($em);
        $this->twigCacheFacade = $twigCacheFacade;
        $this->domain = $domain;
        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * @param string $entityClass
     * @param array $rowIds
     * @throws \App\Twig\Cache\Exception\InvalidCacheLifetimeException
     */
    public function saveOrdering($entityClass, array $rowIds): void
    {
        $normalizedEntityClass = $this->entityNameResolver->resolve($entityClass);
        if (isset(self::ENTITY_TO_CLEAR_CACHE_CONST_PAIRING[$normalizedEntityClass]) === true) {
            foreach ($this->domain->getAllIds() as $domainId) {
                $this->twigCacheFacade->invalidateByKey(self::ENTITY_TO_CLEAR_CACHE_CONST_PAIRING[$normalizedEntityClass], $domainId);
            }
        }
        parent::saveOrdering($entityClass, $rowIds);
    }
}
