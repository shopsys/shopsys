<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Customer\User;

use App\Model\Customer\User\CustomerUserFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

final class CustomerUserRegisteredResolver implements QueryInterface, AliasedInterface
{
    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(CustomerUserFacade $customerUserFacade, Domain $domain)
    {
        $this->customerUserFacade = $customerUserFacade;
        $this->domain = $domain;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function resolveIsCustomerUserRegistered(string $email): bool
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $this->domain->getId());

        return $customerUser !== null;
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveIsCustomerUserRegistered' => 'resolveIsCustomerUserRegistered'];
    }
}
