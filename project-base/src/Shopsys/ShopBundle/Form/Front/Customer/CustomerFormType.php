<?php

namespace Shopsys\ShopBundle\Form\Front\Customer;

use Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface
     */
    private $customerDataFactory;

    public function __construct(CustomerDataFactoryInterface $customerDataFactory)
    {
        $this->customerDataFactory = $customerDataFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userData', UserFormType::class)
            ->add('billingAddressData', BillingAddressFormType::class, [
                'domain_id' => $options['domain_id'],
            ])
            ->add('deliveryAddressData', DeliveryAddressFormType::class, [
                'domain_id' => $options['domain_id'],
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('domain_id')
            ->addAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'empty_data' => $this->customerDataFactory->create(),
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
