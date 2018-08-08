<?php

namespace Shopsys\FrameworkBundle\Model\Article;

class ArticleFactory implements ArticleFactoryInterface
{

    public function create(ArticleData $data): Article
    {
        return new Article($data);
    }
}
