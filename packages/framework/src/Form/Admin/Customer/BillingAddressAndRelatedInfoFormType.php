<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer;

use Shopsys\FrameworkBundle\Form\Constraints\UniqueBillingAddress;
use Shopsys\FrameworkBundle\Form\CustomerUserListType;
use Shopsys\FrameworkBundle\Form\DeliveryAddressListType;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillingAddressAndRelatedInfoFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
                'deleteConfirmMessage' => t('Do you really want to remove this delivery address?'),
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['customer', 'domain_id'])
            ->setAllowedTypes('customer', [Customer::class, 'null'])
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new UniqueBillingAddress([
                        'errorPath' => 'companyNumber',
                    ]),
                ],
            ]);
    }

    /**
     * @return string|null
     */
    public function getParent(): ?string
    {
        return BillingAddressFormType::class;
    }
}
