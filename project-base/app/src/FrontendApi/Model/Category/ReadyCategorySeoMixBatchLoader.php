<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;

class ReadyCategorySeoMixBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        private PromiseAdapter $promiseAdapter,
        private ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        private Domain $domain,
        private FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    /**
     * @param int[] $categoryIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByCategoryIds(array $categoryIds): Promise
    {
        $allReadyCategorySeoMixes = $this->readyCategorySeoMixFacade->getAllIndexedByCategoryId($categoryIds, $this->domain->getCurrentDomainConfig());

        $result = [];

        foreach ($allReadyCategorySeoMixes as $readyCategorySeoMixes) {
            $result[] = array_map(
                fn (ReadyCategorySeoMix $readyCategorySeoMix) => [
                    'name' => $readyCategorySeoMix->getH1(),
                    'slug' => '/' . $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
                        $this->domain->getId(),
                        'front_category_seo',
                        $readyCategorySeoMix->getId(),
                    ),
                ],
                $readyCategorySeoMixes,
            );
        }

        return $this->promiseAdapter->all($result);
    }
}
