<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\CategorySeo;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\Exception\ReadyCategorySeoMixNotFoundUserError;

class ReadyCategorySeoMixQuery extends AbstractQuery
{
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly Domain $domain,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
    ) {
    }

    public function readyCategorySeoMixQuery(string $urlSlug): ReadyCategorySeoMix
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_category_seo',
                $urlSlug,
            );

            return $this->readyCategorySeoMixFacade->getById($friendlyUrl->getEntityId());
        } catch (FriendlyUrlNotFoundException | ReadyCategorySeoMixNotFoundException $exception) {
            throw new ReadyCategorySeoMixNotFoundUserError(sprintf('ReadyCategorySeoMix with URL slug "%s" does not exist.', $urlSlug));
        }
    }
}
