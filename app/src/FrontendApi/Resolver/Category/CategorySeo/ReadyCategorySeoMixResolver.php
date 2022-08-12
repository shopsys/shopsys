<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category\CategorySeo;

use App\FrontendApi\Resolver\Category\Exception\ReadyCategorySeoMixNotFoundUserError;
use App\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\Cdn\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;

class ReadyCategorySeoMixResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private ReadyCategorySeoMixFacade $readyCategorySeoMixFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        FriendlyUrlFacade $friendlyUrlFacade,
        Domain $domain,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
    ) {
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->domain = $domain;
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
    }

    /**
     * @param string $urlSlug
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function resolver(string $urlSlug): ReadyCategorySeoMix
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_category_seo',
                $urlSlug
            );

            return $this->readyCategorySeoMixFacade->getById($friendlyUrl->getEntityId());
        } catch (FriendlyUrlNotFoundException | ReadyCategorySeoMixNotFoundException $exception) {
            throw new ReadyCategorySeoMixNotFoundUserError(sprintf('ReadyCategorySeoMix with URL slug "%s" does not exist.', $urlSlug));
        }
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'readyCategorySeoMix',
        ];
    }
}
