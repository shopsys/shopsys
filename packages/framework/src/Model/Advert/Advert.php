<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var string
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
    protected Collection $categories;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advert
     */
    public function __construct(AdvertData $advert)
    {
        $this->setData($advert);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advert
     */
    public function edit(AdvertData $advert)
    {
        $this->setData($advert);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advert
     */
    protected function setData(AdvertData $advert): void
    {
        $this->domainId = $advert->domainId;
        $this->name = $advert->name;
        $this->type = $advert->type;
        $this->code = $advert->code;
        $this->link = $advert->link;
        $this->positionName = $advert->positionName;
        $this->hidden = $advert->hidden;
        $this->uuid = $advert->uuid ?: Uuid::uuid4()->toString();

        $this->categories = new ArrayCollection();

        if ($this->positionName !== AdvertPositionRegistry::POSITION_PRODUCT_LIST) {
            return;
        }

        foreach ($advert->categories as $category) {
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
    public function getUuid(): string
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
    public function getCategories(): array
    {
        return $this->categories->getValues();
    }
}
