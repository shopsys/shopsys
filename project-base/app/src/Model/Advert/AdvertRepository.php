<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Shopsys\FrameworkBundle\Model\Advert\AdvertRepository as BaseAdvertRepository;

/**
 * @method \App\Model\Advert\Advert|null findById(int $advertId)
 * @method \App\Model\Advert\Advert|null findRandomAdvertByPosition(string $positionName, int $domainId, \App\Model\Category\Category|null $category = null)
 * @method \App\Model\Advert\Advert getById(int $advertId)
 * @method \Doctrine\ORM\QueryBuilder getVisibleAdvertByPositionQueryBuilder(string $positionName, int $domainId, \App\Model\Category\Category|null $category = null)
 */
class AdvertRepository extends BaseAdvertRepository
{
}
