<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Query;

use GraphQL\Language\AST\StringValueNode;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Money\HiddenMoney;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper;
use Symfony\Bundle\SecurityBundle\Security;

class MoneyResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Security $security,
        protected readonly MoneyFormatterHelper $moneyFormatterHelper,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'Money' => [
                self::SERIALIZE => $this->serializeMoney(...),
                self::PARSE_VALUE => $this->parseValue(...),
                self::PARSE_LITERAL => $this->parseLiteral(...),
            ],
        ];
    }

    protected function shouldShowAmount(Money $money): bool
    {
        if ($money instanceof HiddenMoney) {
            return false;
        }

        return
            $this->security->getUser() === null ||
            $this->security->isGranted(CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES);
    }

    protected function serializeMoney(Money $money): string
    {
        if ($this->shouldShowAmount($money)) {
            return $this->moneyFormatterHelper->formatWithMaxFractionDigits($money);
        }

        return HiddenMoney::HIDDEN_FORMAT;
    }

    protected function parseValue(string $value): Money
    {
        return Money::create($value);
    }

    protected function parseLiteral(StringValueNode $valueNode): Money
    {
        return Money::create($valueNode->value);
    }
}
