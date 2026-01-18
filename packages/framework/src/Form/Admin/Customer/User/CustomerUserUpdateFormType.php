<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer\User;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Customer\BillingAddressFormType;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueBillingAddress;
use Shopsys\FrameworkBundle\Form\CustomerUserListType;
use Shopsys\FrameworkBundle\Form\DeliveryAddressListType;
use Shopsys\FrameworkBundle\Form\OrderListType;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CustomerUserUpdateFormType extends AbstractType
{
    public function __construct(
        private readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerUserData', CustomerUserFormType::class, [
                'customerUser' => $options['customerUser'],
                'domain_id' => $options['domain_id'],
                'label' => false,
            ])
            ->add('billingAddressData', BillingAddressFormType::class, [
                'domain_id' => $options['domain_id'],
                'label' => false,
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_customer_list',
                'entity' => $options['customerUser'],
            ]);

        if ($options['customerUser'] === null) {
            return;
        }

        $this->addCustomerUserList($options, $builder);
        $builder->add('deliveryAddresses', DeliveryAddressListType::class, [
            'customerUser' => $options['customerUser'],
        ]);
        $builder->add('orders', OrderListType::class, [
            'customer' => $options['customerUser']->getCustomer(),
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['customerUser', 'domain_id'])
            ->setAllowedTypes('customerUser', [CustomerUser::class, 'null'])
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'empty_data' => $this->customerUserUpdateDataFactory->create(),
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new UniqueBillingAddress([
                        'errorPath' => 'billingAddressData.companyNumber',
                    ]),
                ],
            ]);
    }

    /**
     * @param array<string, mixed> $options
     * @throws \Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException
     */
    private function addCustomerUserList(array $options, FormBuilderInterface $builder): void
    {
        $domain = $this->domain->getDomainConfigById($options['domain_id']);
        $customer = $options['customerUser']->getCustomer();
        $isCompanyUser = $customer->getBillingAddress()->isCompanyCustomer();

        if ($domain->isB2b() && $isCompanyUser) {
            $builder->add('customerUsers', CustomerUserListType::class, [
                'customer' => $customer,
                'allowAdd' => true,
            ]);
        }
    }
}
