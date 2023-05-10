<?php

declare(strict_types=1);

namespace App\Model\Article;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Article\Article as BaseArticle;
use Shopsys\FrameworkBundle\Model\Article\ArticleData as BaseArticleData;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity
 */
class Article extends BaseArticle
{
    public const PLACEMENT_FOOTER_1 = 'footer1';
    public const PLACEMENT_FOOTER_2 = 'footer2';
    public const PLACEMENT_FOOTER_3 = 'footer3';
    public const PLACEMENT_FOOTER_4 = 'footer4';

    public const TYPE_SITE = 'site';
    public const TYPE_LINK = 'link';

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
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /**
     * @param \App\Model\Article\ArticleData $articleData
     */
    public function __construct(BaseArticleData $articleData)
    {
        parent::__construct($articleData);
    }

    /**
     * @param \App\Model\Article\ArticleData $articleData
     */
    public function edit(BaseArticleData $articleData)
    {
        parent::edit($articleData);
    }

    /**
     * @param \App\Model\Article\ArticleData $articleData
     */
    protected function setData(BaseArticleData $articleData): void
    {
        parent::setData($articleData);

        $this->external = $articleData->external;
        $this->type = $articleData->type;
        $this->url = $articleData->url;
    }

    /**
     * @return bool
     */
    public function isExternal(): bool
    {
        return $this->external;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isSiteType(): bool
    {
        return $this->type === self::TYPE_SITE;
    }

    /**
     * @return bool
     */
    public function isLinkType(): bool
    {
        return $this->type === self::TYPE_LINK;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }
}
