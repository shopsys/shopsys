<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Author;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @method \Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorTranslation translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorTranslation> getTranslations()
 */
#[AsMcpTable]
#[ORM\Table(name: 'blog_article_authors')]
#[ORM\Entity]
#[EntityImage]
class BlogArticleAuthor extends AbstractTranslatableEntity implements Presentable
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorTranslation>
     */
    #[Prezent\Translations(targetEntity: BlogArticleAuthorTranslation::class)]
    #[Override]
    protected $translations;

    public function __construct(BlogArticleAuthorData $blogArticleAuthorData)
    {
        $this->translations = new ArrayCollection();
        $this->uuid = $blogArticleAuthorData->uuid ?: Uuid::uuid4()->toString();

        $this->setData($blogArticleAuthorData);
    }

    public function edit(BlogArticleAuthorData $blogArticleAuthorData): void
    {
        $this->setData($blogArticleAuthorData);
    }

    protected function setData(BlogArticleAuthorData $blogArticleAuthorData): void
    {
        $this->name = $blogArticleAuthorData->name;
        $this->setTranslations($blogArticleAuthorData);
    }

    protected function setTranslations(BlogArticleAuthorData $blogArticleAuthorData): void
    {
        foreach ($blogArticleAuthorData->jobTitles as $locale => $jobTitle) {
            $this->translation($locale)->setJobTitle($jobTitle);
        }

        foreach ($blogArticleAuthorData->descriptions as $locale => $description) {
            $this->translation($locale)->setDescription($description);
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getJobTitle(?string $locale = null)
    {
        return $this->translation($locale)->getJobTitle();
    }

    /**
     * @return string|null
     */
    public function getDescription(?string $locale = null)
    {
        return $this->translation($locale)->getDescription();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new BlogArticleAuthorTranslation();
    }

    #[Override]
    public function toHumanReadable(): string
    {
        return $this->name;
    }
}
