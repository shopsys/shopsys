<?php

namespace Shopsys\FrameworkBundle\Model\Article;

interface ArticleFactoryInterface
{
    public function create(ArticleData $data): Article;
}
