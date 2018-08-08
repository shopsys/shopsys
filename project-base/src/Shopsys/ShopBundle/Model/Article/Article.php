<?php

namespace Shopsys\ShopBundle\Model\Article;

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
    protected $createdAt;

    /**
     * @param \Shopsys\ShopBundle\Model\Article\ArticleData $articleData
     */
    public function __construct(BaseArticleData $articleData)
    {
        $this->createdAt = $articleData->createdAt ?? new DateTime();
        parent::__construct($articleData);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Article\ArticleData $articleData
     */
    public function edit(BaseArticleData $articleData)
    {
        $this->createdAt = $articleData->createdAt ?? new DateTime();
        parent::edit($articleData);
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
