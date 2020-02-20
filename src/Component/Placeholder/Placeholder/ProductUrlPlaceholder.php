<?php

declare(strict_types = 1);

namespace App\Component\Placeholder\Placeholder;

use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Symfony\Component\Routing\RouterInterface;

final class ProductUrlPlaceholder extends AbstractPlaceholder
{
    public const NAME = 'productUrl';
    public const READABLE_PATTERN = '{productUrl:123:nepovinna-kotva}{/productUrl}';
    public const PATTERN = '/\{productUrl:([0-9]+)(:(\S+))?\}(.*)\{\/productUrl\}/sU';

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade
     */
    private $productOnCurrentDomainFacade;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade $productOnCurrentDomainFacade
     */
    public function __construct(RouterInterface $router, ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade)
    {
        $this->router = $router;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getPattern(): string
    {
        return self::PATTERN;
    }

    /**
     * @param array $matches
     * @param string|null $locale
     * @return mixed|string
     * @SuppressWarnings("PMD.UnusedPrivateMethod")
     */
    protected function replace(array $matches, ?string $locale)
    {
        $productId = $matches[1];

        try {
            $product = $this->productOnCurrentDomainFacade->getVisibleProductById($productId);
        } catch (ProductNotFoundException $productNotFoundException) {
            $product = null;
        }

        if ($product === null) {
            return str_replace($matches[0], '', $matches[0]);
        }

        $anchor = !empty($matches[3]) ? '#' . $matches[3] : '';

        return $this->router->generate('front_product_detail', ['id' => $product->getId()], RouterInterface::ABSOLUTE_URL) . $anchor;
    }
}
