<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueBillingAddress;
use Shopsys\FrameworkBundle\Form\CustomerUserListType;
use Shopsys\FrameworkBundle\Form\DeliveryAddressListType;
use Shopsys\FrameworkBundle\Form\OrderListType;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BillingAddressAndRelatedInfoFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerUsers', CustomerUserListType::class, [
                'customer' => $options['customer'],
                'allowDelete' => true,
                'allowEdit' => true,
                'allowAdd' => true,
                'deleteConfirmMessage' => t('Do you really want to remove this customer?'),
            ])
            ->add('deliveryAddresses', DeliveryAddressListType::class, [
                'customer' => $options['customer'],
                'allowDelete' => true,
                'allowEdit' => true,
                'allowAdd' => true,
                'deleteConfirmMessage' => t('Do you really want to remove this delivery address?'),
            ])
            ->add('orders', OrderListType::class, [
                'customer' => $options['customer'],
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_customer_list',
                'entity' => $options['customer'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['customer', 'domain_id'])
            ->setAllowedTypes('customer', [Customer::class, 'null'])
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new UniqueBillingAddress(
                        errorPath: 'companyNumber',
                    ),
                ],
            ]);
    }

    #[Override]
    public function getParent(): string
    {
        return BillingAddressFormType::class;
    }
}
