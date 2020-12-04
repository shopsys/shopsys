<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Exception\BrandNotFoundException;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;

class BrandResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    protected $brandFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade
     */
    protected FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        BrandFacade $brandFacade,
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->brandFacade = $brandFacade;
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function resolver(?string $uuid = null, ?string $urlSlug = null): Brand
    {
        if ($uuid !== null) {
            return $this->getByUuid($uuid);
        }

        if ($urlSlug !== null) {
            return $this->getByUrlSlug($urlSlug);
        }

        throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'brand',
        ];
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    protected function getByUuid(string $uuid): Brand
    {
        try {
            return $this->brandFacade->getByUuid($uuid);
        } catch (BrandNotFoundException $brandNotFoundException) {
            throw new UserError($brandNotFoundException->getMessage());
        }
    }

    /**
     * @param string $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    protected function getByUrlSlug(string $urlSlug): Brand
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_brand_detail',
                $urlSlug
            );

            return $this->brandFacade->getById($friendlyUrl->getEntityId());
        } catch (FriendlyUrlNotFoundException | BrandNotFoundException $brandNotFoundException) {
            throw new UserError('Brand with URL slug `' . $urlSlug . '` does not exist.');
        }
    }
}
