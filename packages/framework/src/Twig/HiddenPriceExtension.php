<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleProvider;
use Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HiddenPriceExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleProvider $customerUserRoleProvider
     */
    public function __construct(
        protected readonly CustomerUserRoleProvider $customerUserRoleProvider,
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
        if (!$this->customerUserRoleProvider->canCustomerUserSeePrices($customerUser)) {
            return MoneyFormatterHelper::HIDDEN_FORMAT;
        }

        return $price;
    }
}
