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
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;

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
     * @var \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(BlogCategoryFacade $blogCategoryFacade, Domain $domain, FriendlyUrlFacade $friendlyUrlFacade)
    {
        $this->blogCategoryFacade = $blogCategoryFacade;
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
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
                $blogCategory = $this->getVisibleOnDomainAndSlug($urlSlug);
            } else {
                throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
            }
        } catch (BlogCategoryNotFoundException $blogCategoryNotFoundException) {
            throw new UserError($blogCategoryNotFoundException->getMessage());
        }

        return $blogCategory;
    }

    /**
     * @param string $urlSlug
     * @return \App\Model\Blog\Category\BlogCategory
     */
    private function getVisibleOnDomainAndSlug(string $urlSlug): BlogCategory
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_blogcategory_detail',
                $urlSlug
            );

            return $this->blogCategoryFacade->getVisibleOnDomainById($this->domain->getId(), $friendlyUrl->getEntityId());
        } catch (FriendlyUrlNotFoundException | BlogCategoryNotFoundException $blogCategoryNotFoundException) {
            throw new UserError(sprintf('No visible blog category was found by slug "%s"', $urlSlug));
        }
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
