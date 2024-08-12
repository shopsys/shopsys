<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HiddenPriceExtension extends AbstractExtension
{
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
        if ($customerUser !== null && !$customerUser->canSeePrices()) {
            return MoneyFormatterHelper::HIDDEN_FORMAT;
        }

        return $price;
    }
}
