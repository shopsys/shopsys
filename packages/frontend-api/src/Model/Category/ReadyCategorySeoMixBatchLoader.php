<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;

class ReadyCategorySeoMixBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver $customerUserRoleResolver
     */
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
    ) {
    }

    /**
     * @param int[] $categoryIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByCategoryIds(array $categoryIds): Promise
    {
        $allReadyCategorySeoMixes = $this->readyCategorySeoMixFacade->getAllIndexedByCategoryId($categoryIds, $this->domain->getCurrentDomainConfig());

        $canSeePrices = $this->customerUserRoleResolver->canCurrentCustomerUserSeePrices();

        $result = [];

        foreach ($allReadyCategorySeoMixes as $readyCategorySeoMixes) {
            $filteredMixes = $canSeePrices
                ? $readyCategorySeoMixes
                : array_filter($readyCategorySeoMixes, fn (ReadyCategorySeoMix $mix) => !$mix->hasPriceBasedOrdering());

            $result[] = array_map(
                fn (ReadyCategorySeoMix $readyCategorySeoMix) => [
                    'name' => $readyCategorySeoMix->getH1(),
                    'slug' => '/' . $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
                        $this->domain->getId(),
                        'front_category_seo',
                        $readyCategorySeoMix->getId(),
                    ),
                ],
                $filteredMixes,
            );
        }

        return $this->promiseAdapter->all($result);
    }
}
