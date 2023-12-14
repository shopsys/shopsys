<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity
 */
class Article implements OrderableEntityInterface
{
    public const PLACEMENT_NONE = 'none';
    public const PLACEMENT_FOOTER_1 = 'footer1';
    public const PLACEMENT_FOOTER_2 = 'footer2';
    public const PLACEMENT_FOOTER_3 = 'footer3';
    public const PLACEMENT_FOOTER_4 = 'footer4';

    public const TYPE_SITE = 'site';
    public const TYPE_LINK = 'link';

    protected const GEDMO_SORTABLE_LAST_POSITION = -1;

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
     * @Gedmo\SortableGroup
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var int
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoTitle;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoMetaDescription;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoH1;

    /**
     * @var string
     * @Gedmo\SortableGroup
     * @ORM\Column(type="text")
     */
    protected $placement;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $external;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     */
    public function __construct(ArticleData $articleData)
    {
        $this->domainId = $articleData->domainId;
        $this->position = static::GEDMO_SORTABLE_LAST_POSITION;
        $this->uuid = $articleData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($articleData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     */
    public function edit(ArticleData $articleData)
    {
        $this->setData($articleData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     */
    protected function setData(ArticleData $articleData): void
    {
        $this->name = $articleData->name;
        $this->text = $articleData->text;
        $this->seoTitle = $articleData->seoTitle;
        $this->seoMetaDescription = $articleData->seoMetaDescription;
        $this->seoH1 = $articleData->seoH1;
        $this->placement = $articleData->placement;
        $this->hidden = $articleData->hidden;
        $this->createdAt = $articleData->createdAt ?? new DateTime();
        $this->external = $articleData->external;
        $this->type = $articleData->type;
        $this->url = $articleData->url;
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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription()
    {
        return $this->seoMetaDescription;
    }

    /**
     * @return string|null
     */
    public function getSeoH1()
    {
        return $this->seoH1;
    }

    /**
     * @return string
     */
    public function getPlacement()
    {
        return $this->placement;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param string $placement
     */
    public function setPlacement($placement)
    {
        $this->placement = $placement;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return bool
     */
    public function isExternal()
    {
        return $this->external;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isLinkType()
    {
        return $this->type === self::TYPE_LINK;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
