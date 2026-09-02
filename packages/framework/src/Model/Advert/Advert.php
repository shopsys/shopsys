<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImageFolder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'adverts')]
#[ORM\Entity]
#[EntityImageFolder('noticer')]
#[EntityImage]
#[EntityImage('web')]
#[EntityImage('mobile')]
class Advert implements DomainSeparatedEntityInterface
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_CODE = 'code';

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid {
        set {
            $this->uuid = $value ?: Uuid::uuid4()->toString();
        }
    }

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text')]
    protected $name;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text')]
    protected $type;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $code;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $link;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text')]
    protected $positionName;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $hidden;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Category\Category>
     */
    #[ORM\JoinTable(name: 'advert_categories')]
    #[ORM\ManyToMany(targetEntity: Category::class)]
    protected $categories;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $datetimeVisibleFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $datetimeVisibleTo;

    public function __construct(AdvertData $advertData)
    {
        $this->setData($advertData);
    }

    public function edit(AdvertData $advertData): void
    {
        $this->setData($advertData);
    }

    protected function setData(AdvertData $advertData): void
    {
        $this->domainId = $advertData->domainId;
        $this->name = $advertData->name;
        $this->type = $advertData->type;
        $this->code = $advertData->code;
        $this->link = $advertData->link;
        $this->positionName = $advertData->positionName;
        $this->hidden = $advertData->hidden;
        $this->uuid = $advertData->uuid;

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
    #[Override]
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
     * @return \DateTimeImmutable|null
     */
    public function getDatetimeVisibleFrom()
    {
        return $this->datetimeVisibleFrom;
    }

    /**
     * @return \DateTimeImmutable|null
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
