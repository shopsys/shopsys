<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Author;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpInheritedColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[AsMcpInheritedColumn(fieldName: 'id')]
#[AsMcpInheritedColumn(fieldName: 'locale')]
#[ORM\Table(name: 'blog_article_author_translations')]
#[ORM\Entity]
class BlogArticleAuthorTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor
     */
    #[AsMcpColumn]
    #[Prezent\Translatable(targetEntity: BlogArticleAuthor::class)]
    #[Override]
    protected $translatable;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $jobTitle {
        set {
            $this->jobTitle = TransformStringHelper::getTrimmedStringOrNullOnEmpty($value);
        }
    }

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $description {
        set {
            $this->description = TransformStringHelper::getTrimmedStringOrNullOnEmpty($value);
        }
    }

    /**
     * @return string|null
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param string|null $jobTitle
     */
    public function setJobTitle($jobTitle): void
    {
        $this->jobTitle = $jobTitle;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }
}
