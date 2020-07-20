<?php

declare(strict_types=1);

namespace App\Model\Product\TopProduct;

use App\Twig\Cache\TwigCacheFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade as BaseTopProductFacade;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductRepository;

/**
 * @method \App\Model\Product\Product[] getAllOfferedProducts(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 */
class TopProductFacade extends BaseTopProductFacade
{
    /**
     * @var \App\Twig\Cache\TwigCacheFacade
     */
    private $twigCacheFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductRepository $topProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFactoryInterface $topProductFactory
     * @param \App\Twig\Cache\TwigCacheFacade $twigCacheFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        TopProductRepository $topProductRepository,
        TopProductFactoryInterface $topProductFactory,
        TwigCacheFacade $twigCacheFacade
    ) {
        parent::__construct($em, $topProductRepository, $topProductFactory);
        $this->twigCacheFacade = $twigCacheFacade;
    }

    /**
     * @param int $domainId
     * @param \App\Model\Product\Product[] $products
     * @throws \App\Twig\Cache\Exception\InvalidCacheLifetimeException
     */
    public function saveTopProductsForDomain($domainId, array $products): void
    {
        parent::saveTopProductsForDomain($domainId, $products);
        $this->twigCacheFacade->invalidateByKey($this->twigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE, $domainId);
    }
}
