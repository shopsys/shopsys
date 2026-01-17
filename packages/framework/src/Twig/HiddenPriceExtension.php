<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Money\HiddenMoney;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HiddenPriceExtension extends AbstractExtension
{
    public function __construct(
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
    ) {
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'hidePrice',
                $this->hidePriceFilter(...),
            ),
        ];
    }

    public function hidePriceFilter(string $price, ?CustomerUser $customerUser): string
    {
        if (!$this->customerUserRoleResolver->canCustomerUserSeePrices($customerUser)) {
            return HiddenMoney::HIDDEN_FORMAT;
        }

        return $price;
    }
}
