<?php

declare(strict_types=1);

namespace App\Model\Article;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Article\Article as BaseArticle;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity
 */
class Article extends BaseArticle
{
}
