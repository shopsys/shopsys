<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use DateTime;
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
    protected Collection $categories;

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
    public function edit(AdvertData $advertData): void
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
    public function getId(): int
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
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @return string|null
     */
    public function getPositionName(): ?string
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

    /**
     * @return \DateTime|null
     */
    public function getDatetimeVisibleFrom(): ?DateTime
    {
        return $this->datetimeVisibleFrom;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeVisibleTo(): ?DateTime
    {
        return $this->datetimeVisibleTo;
    }
}
