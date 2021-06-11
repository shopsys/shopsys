<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Blog\Article;

use App\FrontendApi\Model\Blog\Article\BlogArticleElasticFacade;
use App\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use DateTime;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Ramsey\Uuid\Uuid;

class BlogArticleResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\FrontendApi\Model\Blog\Article\BlogArticleElasticFacade
     */
    private BlogArticleElasticFacade $blogArticleElasticFacade;

    /**
     * @param \App\FrontendApi\Model\Blog\Article\BlogArticleElasticFacade $blogArticleElasticFacade
     */
    public function __construct(BlogArticleElasticFacade $blogArticleElasticFacade)
    {
        $this->blogArticleElasticFacade = $blogArticleElasticFacade;
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function resolver(string $uuid): array
    {
        if (Uuid::isValid($uuid) === false) {
            throw new UserError('Provided argument is not valid UUID.');
        }

        try {
            $blogArticleData = $this->blogArticleElasticFacade->getByUuid($uuid);
        } catch (BlogArticleNotFoundException $blogArticleNotFoundException) {
            throw new UserError($blogArticleNotFoundException->getMessage());
        }

        $this->setDateTimeBlogArticleData($blogArticleData);

        return $blogArticleData;
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'blogArticle',
        ];
    }

    /**
     * @param array $blogArticleData
     */
    private function setDateTimeBlogArticleData(array &$blogArticleData)
    {
        $blogArticleData['createdAt'] = new DateTime($blogArticleData['createdAt']);
        $blogArticleData['publishDate'] = new DateTime($blogArticleData['publishDate']);
    }
}
