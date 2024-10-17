<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category\CategorySeo;

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
    /**
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
        private readonly Domain $domain,
        private readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
    ) {
    }

    /**
     * @param string $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix
     */
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
