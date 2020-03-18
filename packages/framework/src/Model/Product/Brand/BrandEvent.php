<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Symfony\Contracts\EventDispatcher\Event;

class BrandEvent extends Event
{
    /**
     * The CREATE event occurs once a brand was created.
     *
     * This event allows you to run jobs dependent on the brand creation.
     */
    public const CREATE = 'brand.create';
    /**
     * The UPDATE event occurs once a brand was changed.
     *
     * This event allows you to run jobs dependent on the brand change.
     */
    public const UPDATE = 'brand.update';
    /**
     * The DELETE event occurs once a brand was deleted.
     *
     * This event allows you to run jobs dependent on the brand deletion.
     */
    public const DELETE = 'brand.delete';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    protected $brand;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     */
    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }
}
