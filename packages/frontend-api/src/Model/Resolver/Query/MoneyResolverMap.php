<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Query;

use GraphQL\Language\AST\StringValueNode;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Money\HiddenMoney;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper;
use Symfony\Component\Security\Core\Security;

class MoneyResolverMap extends ResolverMap
{
    /**
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(protected readonly Security $security)
    {
    }

    /**
     * @return array
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return bool
     */
    protected function shouldShowAmount(Money $money): bool
    {
        if ($money instanceof HiddenMoney) {
            return false;
        }

        return
            $this->security->getUser() === null ||
            $this->security->isGranted(CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return string
     */
    protected function serializeMoney(Money $money): string
    {
        if ($this->shouldShowAmount($money)) {
            return MoneyFormatterHelper::formatWithMaxFractionDigits($money);
        }

        return MoneyFormatterHelper::HIDDEN_FORMAT;
    }

    /**
     * @param string $value
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function parseValue(string $value): Money
    {
        return Money::create($value);
    }

    /**
     * @param \GraphQL\Language\AST\StringValueNode $valueNode
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function parseLiteral(StringValueNode $valueNode): Money
    {
        return Money::create($valueNode->value);
    }
}
