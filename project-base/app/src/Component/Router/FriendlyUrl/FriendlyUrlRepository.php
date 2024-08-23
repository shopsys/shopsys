<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use App\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository as BaseFriendlyUrlRepository;

class FriendlyUrlRepository extends BaseFriendlyUrlRepository
{
    /**
     * @return array<string, string>
     */
    public function getRouteNameToEntityMap(): array
    {
        return array_merge(
            parent::getRouteNameToEntityMap(),
            [
                'front_category_seo' => ReadyCategorySeoMix::class,
            ],
        );
    }
}
