<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Flag;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Flag\Exception\FlagNotFoundUserError;

class FlagQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        protected readonly FlagFacade $flagFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function flagByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): Flag
    {
        if ($uuid !== null) {
            try {
                return $this->flagFacade->getVisibleByUuid($uuid, $this->domain->getLocale());
            } catch (FlagNotFoundException $flagNotFoundException) {
                throw new FlagNotFoundUserError($flagNotFoundException->getMessage());
            }
        }

        if ($urlSlug !== null) {
            return $this->getVisibleOnDomainBySlug($urlSlug);
        }

        throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @param string $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    protected function getVisibleOnDomainBySlug(string $urlSlug): Flag
    {
        $urlSlug = ltrim($urlSlug, '/');

        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_flag_detail',
                $urlSlug,
            );

            return $this->flagFacade->getVisibleFlagById($friendlyUrl->getEntityId(), $this->domain->getLocale());
        } catch (FriendlyUrlNotFoundException|FlagNotFoundException $flagNotFoundException) {
            throw new FlagNotFoundUserError(sprintf('Flag with URL slug "%s" does not exist.', $urlSlug));
        }
    }
}
