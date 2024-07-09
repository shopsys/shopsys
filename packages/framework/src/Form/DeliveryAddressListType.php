<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Form\Exception\InvalidOptionException;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryAddressListType extends AbstractType
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['customer', 'customerUser'])
            ->setAllowedTypes('customer', [Customer::class, 'null'])
            ->setAllowedTypes('customerUser', [CustomerUser::class, 'null'])
            ->setDefaults([
                'customer' => null,
                'customerUser' => null,
                'mapped' => false,
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        if ($options['customer'] === null && $options['customerUser'] === null) {
            throw new InvalidOptionException('An option "customer" or "customerUser" must be set.');
        }

        $deliveryAddresses = [];

        if ($options['customer'] !== null) {
            $deliveryAddresses = $options['customer']->getDeliveryAddresses();
        } elseif ($options['customerUser'] !== null) {
            $deliveryAddresses = $options['customerUser']->getCustomer()->getDeliveryAddresses();
        }
        $view->vars['deliveryAddresses'] = $deliveryAddresses;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return FormType::class;
    }
}
