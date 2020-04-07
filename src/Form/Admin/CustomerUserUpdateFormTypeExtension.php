<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Shopsys\FrameworkBundle\Form\Admin\Customer\BillingAddressFormType;
use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserUpdateFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerUserUpdateFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('billingAddressData', BillingAddressFormType::class, [
                'customerUser' => $options['customerUser'],
                'domain_id' => $options['domain_id'],
                'render_form_row' => false,
                'attr' => [
                    'class' => 'wrap-divider',
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CustomerUserUpdateFormType::class;
    }
}
