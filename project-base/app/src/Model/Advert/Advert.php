<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Advert\Advert as BaseAdvert;

/**
 * @ORM\Table(name="adverts")
 * @ORM\Entity
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Category\Category> $categories
 * @method __construct(\App\Model\Advert\AdvertData $advertData)
 * @method edit(\App\Model\Advert\AdvertData $advertData)
 * @method setData(\App\Model\Advert\AdvertData $advertData)
 * @method \App\Model\Category\Category[] getCategories()
 */
class Advert extends BaseAdvert
{
}
