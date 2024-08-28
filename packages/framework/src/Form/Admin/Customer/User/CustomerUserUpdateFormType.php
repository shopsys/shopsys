<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer\User;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Customer\BillingAddressFormType;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueBillingAddress;
use Shopsys\FrameworkBundle\Form\CustomerUserListType;
use Shopsys\FrameworkBundle\Form\DeliveryAddressListType;
use Shopsys\FrameworkBundle\Form\OrderListType;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUserUpdateFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerUserData', CustomerUserFormType::class, [
                'customerUser' => $options['customerUser'],
                'domain_id' => $options['domain_id'],
                'render_form_row' => false,
                'attr' => [
                    'class' => 'wrap-divider',
                ],
            ])
            ->add('billingAddressData', BillingAddressFormType::class, [
                'domain_id' => $options['domain_id'],
                'render_form_row' => false,
                'attr' => [
                    'class' => 'wrap-divider',
                ],
            ])
            ->add('save', SubmitType::class);

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

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
     * @param array $options
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
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
