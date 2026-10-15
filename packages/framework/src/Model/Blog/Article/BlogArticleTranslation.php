<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpInheritedColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[AsMcpInheritedColumn(fieldName: 'id')]
#[AsMcpInheritedColumn(fieldName: 'locale')]
#[ORM\Table(name: 'blog_article_translations')]
#[ORM\Entity]
class BlogArticleTranslation extends AbstractTranslation
{
    #[AsMcpColumn]
    #[Prezent\Translatable(targetEntity: BlogArticle::class)]
    #[Override]
    protected $translatable;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $name {
        set {
            $this->name = ExtendedClassNameResolver::resolve(TransformStringHelper::class)::getTrimmedStringOrNullOnEmpty($value);
        }
    }

    /**
     * Content editor document, see \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument::toArray()
     *
     * @var array|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    protected $content;

    /**
     * HTML written by GrapesJS before the content editor, it is read only by the migration task converting it
     * to the content and is dropped in the next release. Application code must never write it.
     *
     * @var string|null
     */
    #[AsMcpColumn(exposed: false)]
    #[ORM\Column(name: 'legacy_grapesjs_description', type: Types::TEXT, nullable: true)]
    protected $legacyGrapesJsDescription;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $perex {
        set {
            $this->perex = ExtendedClassNameResolver::resolve(TransformStringHelper::class)::getTrimmedStringOrNullOnEmpty($value);
        }
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return array|null
     */
    public function getContentArray()
    {
        return $this->content;
    }

    /**
     * @param array|null $content
     */
    public function setContentArray($content): void
    {
        $this->content = $content;
    }

    /**
     * @return string|null
     */
    public function getPerex()
    {
        return $this->perex;
    }

    /**
     * @param string|null $perex
     */
    public function setPerex($perex): void
    {
        $this->perex = $perex;
    }
}
