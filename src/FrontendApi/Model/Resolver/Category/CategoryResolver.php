<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Category;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolver as BaseCategoryResolver;

class CategoryResolver extends BaseCategoryResolver
{
    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \App\Model\Category\Category
     */
    public function resolveByUuidOrUrlSlug(?string $uuid = null, ?string $urlSlug = null): Category
    {
        if ($uuid !== null) {
            /** @var \App\Model\Category\Category $category */
            $category = $this->getByUuid($uuid);

            return $category;
        }

        if ($urlSlug !== null) {
            $urlSlug = ltrim($urlSlug, '/');

            /** @var \App\Model\Category\Category $category */
            $category = $this->getVisibleOnDomainAndSlug($urlSlug);

            return $category;
        }

        throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }
}
