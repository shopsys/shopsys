<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ReadyCategorySeoMixBatchLoader
{
    /**
     * @var \GraphQL\Executor\Promise\PromiseAdapter
     */
    private PromiseAdapter $promiseAdapter;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private ReadyCategorySeoMixFacade $readyCategorySeoMixFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        PromiseAdapter $promiseAdapter,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->promiseAdapter = $promiseAdapter;
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
                    'slug' => $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
                        $this->domain->getId(),
                        'front_category_seo',
                        $readyCategorySeoMix->getId()
                    ),
                ],
                $readyCategorySeoMixes
            );
        }

        return $this->promiseAdapter->all($result);
    }
}
