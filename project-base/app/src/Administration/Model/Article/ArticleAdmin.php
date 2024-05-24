<?php

declare(strict_types=1);

namespace App\Administration\Model\Article;

use Shopsys\Administration\Model\Article\ArticleAdmin as BaseArticleAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;

class ArticleAdmin extends BaseArticleAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);

        $list->remove('id');
        $list->add('createdAt');

        $list->reorder(['name', 'createdAt', ListMapper::NAME_ACTIONS]);
    }
}
