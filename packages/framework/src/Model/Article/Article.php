<?php

namespace Shopsys\FrameworkBundle\Model\Article;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity
 */
class Article implements OrderableEntityInterface
{
    const PLACEMENT_TOP_MENU = 'topMenu';
    const PLACEMENT_FOOTER = 'footer';
    const PLACEMENT_NONE = 'none';

    private const GEDMO_SORTABLE_LAST_POSITION = -1;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     *
     * @Gedmo\SortableGroup
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var int
     *
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoTitle;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoMetaDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoH1;

    /**
     * @var string
     *
     * @Gedmo\SortableGroup
     * @ORM\Column(type="text")
     */
    protected $placement;

    /**
     * @var string
     *
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    public function __construct(ArticleData $articleData)
    {
        $this->domainId = $articleData->domainId;
        $this->name = $articleData->name;
        $this->text = $articleData->text;
        $this->seoTitle = $articleData->seoTitle;
        $this->seoMetaDescription = $articleData->seoMetaDescription;
        $this->seoH1 = $articleData->seoH1;
        $this->placement = $articleData->placement;
        $this->position = self::GEDMO_SORTABLE_LAST_POSITION;
        $this->hidden = $articleData->hidden;
    }

    public function edit(ArticleData $articleData): void
    {
        $this->name = $articleData->name;
        $this->text = $articleData->text;
        $this->seoTitle = $articleData->seoTitle;
        $this->seoMetaDescription = $articleData->seoMetaDescription;
        $this->seoH1 = $articleData->seoH1;
        $this->placement = $articleData->placement;
        $this->hidden = $articleData->hidden;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function getSeoMetaDescription(): ?string
    {
        return $this->seoMetaDescription;
    }

    public function getSeoH1(): ?string
    {
        return $this->seoH1;
    }

    public function getPlacement(): string
    {
        return $this->placement;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function setPlacement(string $placement): void
    {
        $this->placement = $placement;
    }

    /**
     * @return bool $visible
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
