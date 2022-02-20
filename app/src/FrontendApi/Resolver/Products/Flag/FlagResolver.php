<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\Flag;

use App\Model\Product\Flag\Flag;
use App\Model\Product\Flag\FlagFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagNotFoundException;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;

class FlagResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\Product\Flag\FlagFacade
     */
    private FlagFacade $flagFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(FlagFacade $flagFacade, Domain $domain, FriendlyUrlFacade $friendlyUrlFacade)
    {
        $this->flagFacade = $flagFacade;
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \App\Model\Product\Flag\Flag
     */
    public function resolveByUuidOrUrlSlug(?string $uuid = null, ?string $urlSlug = null): Flag
    {
        if ($uuid !== null) {
            try {
                return $this->flagFacade->getVisibleByUuid($uuid, $this->domain->getLocale());
            } catch (FlagNotFoundException $flagNotFoundException) {
                throw new UserError($flagNotFoundException->getMessage());
            }
        }

        if ($urlSlug !== null) {
            return $this->getVisibleOnDomainBySlug($urlSlug);
        }

        throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @param string $urlSlug
     * @return \App\Model\Product\Flag\Flag
     */
    protected function getVisibleOnDomainBySlug(string $urlSlug): Flag
    {
        $urlSlug = ltrim($urlSlug, '/');

        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_flag_detail',
                $urlSlug
            );

            return $this->flagFacade->getVisibleFlagById($friendlyUrl->getEntityId(), $this->domain->getLocale());
        } catch (FriendlyUrlNotFoundException | FlagNotFoundException $flagNotFoundException) {
            throw new UserError(sprintf('Flag with URL slug "%s" does not exist.', $urlSlug));
        }
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolveByUuidOrUrlSlug' => 'flag',
        ];
    }
}
