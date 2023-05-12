<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\String\TransformString;

class FriendlyUrlFacade
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     */
    public function __construct(protected readonly FriendlyUrlRepository $friendlyUrlRepository)
    {
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param string $slug
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
     */
    public function getFriendlyUrlByRouteNameAndSlug(int $domainId, string $routeName, string $slug): FriendlyUrl
    {
        $friendlyUrl = $this->friendlyUrlRepository->findFriendlyUrlBySlugAndRouteName($domainId, $routeName, $slug);

        if ($friendlyUrl === null) {
            $modifiedSlug = TransformString::addOrRemoveTrailingSlashFromString($slug);
            $friendlyUrl = $this->friendlyUrlRepository->findFriendlyUrlBySlugAndRouteName(
                $domainId,
                $routeName,
                $modifiedSlug
            );
        }

        if ($friendlyUrl === null) {
            $message = sprintf(
                'Friendly url not found for route `%s` by slug `%s` on domain `%s`',
                $routeName,
                $slug,
                $domainId
            );

            throw new FriendlyUrlNotFoundException($message);
        }

        return $friendlyUrl;
    }
}
