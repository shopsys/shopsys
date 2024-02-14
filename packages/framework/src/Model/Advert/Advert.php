<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="adverts")
 * @ORM\Entity
 */
class Advert
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_CODE = 'code';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $type;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $code;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $link;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $positionName;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Category\Category>
     * @ORM\ManyToMany(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category")
     * @ORM\JoinTable(name="advert_categories")
     */
    protected $categories;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $datetimeVisibleFrom;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $datetimeVisibleTo;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advertData
     */
    public function __construct(AdvertData $advertData)
    {
        $this->setData($advertData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advertData
     */
    public function edit(AdvertData $advertData)
    {
        $this->setData($advertData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advertData
     */
    protected function setData(AdvertData $advertData): void
    {
        $this->domainId = $advertData->domainId;
        $this->name = $advertData->name;
        $this->type = $advertData->type;
        $this->code = $advertData->code;
        $this->link = $advertData->link;
        $this->positionName = $advertData->positionName;
        $this->hidden = $advertData->hidden;
        $this->uuid = $advertData->uuid ?: Uuid::uuid4()->toString();

        $this->datetimeVisibleFrom = $advertData->datetimeVisibleFrom;
        $this->datetimeVisibleTo = $advertData->datetimeVisibleTo;

        $this->categories = new ArrayCollection();

        if (!AdvertPositionRegistry::isCategoryPosition($this->positionName)) {
            return;
        }

        foreach ($advertData->categories as $category) {
            $this->categories->add($category);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @return string|null
     */
    public function getPositionName()
    {
        return $this->positionName;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategories()
    {
        return $this->categories->getValues();
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeVisibleFrom()
    {
        return $this->datetimeVisibleFrom;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeVisibleTo()
    {
        return $this->datetimeVisibleTo;
    }

    /**
     * @return int[]
     */
    public function getCategoryIds(): array
    {
        $categoryIds = [];

        foreach ($this->getCategories() as $category) {
            $categoryIds[] = $category->getId();
        }

        return $categoryIds;
    }
}
