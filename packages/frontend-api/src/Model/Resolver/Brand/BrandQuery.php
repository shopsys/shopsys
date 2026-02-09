<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Exception\BrandNotFoundException;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\Exception\BrandNotFoundUserError;

class BrandQuery extends AbstractQuery
{
    public function __construct(
        protected readonly BrandFacade $brandFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    public function brandByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): Brand
    {
        if ($uuid !== null) {
            return $this->getByUuid($uuid);
        }

        if ($urlSlug !== null) {
            return $this->getByUrlSlug($urlSlug);
        }

        throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    protected function getByUuid(string $uuid): Brand
    {
        try {
            return $this->brandFacade->getByUuid($uuid);
        } catch (BrandNotFoundException $brandNotFoundException) {
            throw new BrandNotFoundUserError($brandNotFoundException->getMessage());
        }
    }

    protected function getByUrlSlug(string $urlSlug): Brand
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_brand_detail',
                $urlSlug,
            );

            return $this->brandFacade->getById($friendlyUrl->getEntityId());
        } catch (FriendlyUrlNotFoundException | BrandNotFoundException $brandNotFoundException) {
            throw new BrandNotFoundUserError('Brand with URL slug `' . $urlSlug . '` does not exist.');
        }
    }
}
