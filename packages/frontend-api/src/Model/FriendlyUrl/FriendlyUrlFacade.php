<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;

class FriendlyUrlFacade
{
    public function __construct(
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    public function getFriendlyUrlByRouteNameAndSlug(int $domainId, string $routeName, string $slug): FriendlyUrl
    {
        $friendlyUrl = $this->friendlyUrlRepository->findFriendlyUrlBySlugAndRouteName($domainId, $routeName, $slug);

        if ($friendlyUrl === null) {
            $modifiedSlug = $this->transformStringHelper->addOrRemoveTrailingSlashFromString($slug);
            $friendlyUrl = $this->friendlyUrlRepository->findFriendlyUrlBySlugAndRouteName(
                $domainId,
                $routeName,
                $modifiedSlug,
            );
        }

        if ($friendlyUrl === null) {
            $message = sprintf(
                'Friendly url not found for route `%s` by slug `%s` on domain `%s`',
                $routeName,
                $slug,
                $domainId,
            );

            throw new FriendlyUrlNotFoundException($message);
        }

        return $friendlyUrl;
    }
}
