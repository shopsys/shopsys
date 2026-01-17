<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Category\Category;

#[ORM\Table(name: 'autocomplete_favorite_categories')]
#[ORM\Entity]
class AutocompleteFavoriteCategory implements OrderableEntityInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\Id]
    protected $category;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    protected $domainId;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $position;

    public function __construct(Category $category, int $domainId, int $position)
    {
        $this->category = $category;
        $this->domainId = $domainId;
        $this->position = $position;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getDomainId()
    {
        return $this->domainId;
    }

    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    #[Override]
    public function setPosition($position): void
    {
        $this->position = $position;
    }
}
