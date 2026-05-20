<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'promo_code_categories')]
#[ORM\Entity]
class PromoCodeCategory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'promo_code_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: PromoCode::class)]
    #[ORM\Id]
    protected $promoCode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'category_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\Id]
    protected $category;

    public function __construct(PromoCode $promoCode, Category $category)
    {
        $this->promoCode = $promoCode;
        $this->category = $category;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
