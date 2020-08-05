<?php

declare(strict_types=1);

namespace App\Model\Article;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Article\Article as BaseArticle;
use Shopsys\FrameworkBundle\Model\Article\ArticleData as BaseArticleData;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity
 */
class Article extends BaseArticle
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

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
        $this->createdAt = $articleData->createdAt ?? new DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
