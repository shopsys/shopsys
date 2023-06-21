<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade as BaseAdvertFacade;

/**
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Advert\AdvertRepository $advertRepository, \App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Advert\AdvertFactory $advertFactory, \App\Model\Advert\AdvertPositionRegistry $advertPositionRegistry)
 * @method \App\Model\Advert\Advert getById(int $advertId)
 * @method \App\Model\Advert\Advert|null findRandomAdvertByPositionOnCurrentDomain(string $positionName, \App\Model\Category\Category|null $category = null)
 * @method \App\Model\Advert\Advert create(\App\Model\Advert\AdvertData $advertData)
 * @method \App\Model\Advert\Advert edit(int $advertId, \App\Model\Advert\AdvertData $advertData)
 */
class AdvertFacade extends BaseAdvertFacade
{
}
