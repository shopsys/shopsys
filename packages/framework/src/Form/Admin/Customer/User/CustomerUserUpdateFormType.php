<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Customer\User;

use Shopsys\FrameworkBundle\Form\Admin\Customer\BillingAddressFormType;
use Shopsys\FrameworkBundle\Form\DeliveryAddressListType;
use Shopsys\FrameworkBundle\Form\OrderListType;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUserUpdateFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface
     */
    private $customerUserUpdateDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     */
    public function __construct(CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory)
    {
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
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
            $builder->add('sendRegistrationMail', CheckboxType::class, [
                'required' => false,
                'label' => t('Send confirmation e-mail about registration to customer'),
            ]);
        } else {
            $builder->add('deliveryAddresses', DeliveryAddressListType::class, [
                'customerUser' => $options['customerUser'],
            ]);
            $builder->add('orders', OrderListType::class, [
                'customerUser' => $options['customerUser'],
            ]);
        }
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
            ]);
    }
}
