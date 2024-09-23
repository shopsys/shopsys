<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HiddenPriceExtension extends AbstractExtension
{
    protected const string HIDDEN_FORMAT = '***';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver $customerUserRoleResolver
     */
    public function __construct(
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
    ) {
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'hidePrice',
                $this->hidePriceFilter(...),
            ),
        ];
    }

    /**
     * @param string $price
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return string
     */
    public function hidePriceFilter(string $price, ?CustomerUser $customerUser): string
    {
        if (!$this->customerUserRoleResolver->canCustomerUserSeePrices($customerUser)) {
            return static::HIDDEN_FORMAT;
        }

        return $price;
    }
}
