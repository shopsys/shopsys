<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\Category;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="category_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="category_domain", columns={"category_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class CategoryDomain
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Tests\App\Functional\EntityExtension\Model\Category\Category", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected Category $category;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $domainId;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $enabled;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $description = null;
}
