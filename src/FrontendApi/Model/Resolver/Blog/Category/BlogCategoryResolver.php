<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Blog\Category;

use App\Model\Blog\Category\BlogCategory;
use App\Model\Blog\Category\BlogCategoryFacade;
use App\Model\Blog\Category\Exception\BlogCategoryNotFoundException;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\Cdn\Component\Domain\Domain;

class BlogCategoryResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\Blog\Category\BlogCategoryFacade
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     */
    public function __construct(BlogCategoryFacade $blogCategoryFacade, Domain $domain)
    {
        $this->blogCategoryFacade = $blogCategoryFacade;
        $this->domain = $domain;
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \App\Model\Blog\Category\BlogCategory
     */
    public function resolveByUuidOrUrlSlug(?string $uuid = null, ?string $urlSlug = null): BlogCategory
    {
        try {
            $domainId = $this->domain->getId();
            if ($uuid !== null) {
                $blogCategory = $this->blogCategoryFacade->getVisibleByUuid($domainId, $uuid);
            } elseif ($urlSlug !== null) {
                $blogCategory = $this->blogCategoryFacade->getVisibleByUrlSlug($domainId, $urlSlug);
            } else {
                throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
            }
        } catch (BlogCategoryNotFoundException $blogCategoryNotFoundException) {
            throw new UserError($blogCategoryNotFoundException->getMessage());
        }

        return $blogCategory;
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolveByUuidOrUrlSlug' => 'blogCategoryByUuidOrUrlSlug',
        ];
    }
}
