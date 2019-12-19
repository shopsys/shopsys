<?php

declare(strict_types=1);

namespace App\Form\Front\Customer\User;

use App\Form\Front\Customer\BillingAddressFormType;
use App\Form\Front\Customer\DeliveryAddressFormType;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Symfony\Component\Form\AbstractType;
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
            ->add('customerUserData', CustomerUserFormType::class)
            ->add('billingAddressData', BillingAddressFormType::class, [
                'domain_id' => $options['domain_id'],
            ])
            ->add('deliveryAddressData', DeliveryAddressFormType::class, [
                'domain_id' => $options['domain_id'],
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('domain_id')
            ->addAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'empty_data' => $this->customerUserUpdateDataFactory->create(),
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
