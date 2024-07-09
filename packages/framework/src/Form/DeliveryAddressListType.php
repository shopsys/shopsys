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
        $resolver->setDefined(['customer', 'customerUser', 'allowDelete', 'deleteConfirmMessage', 'allowEdit', 'allowAdd'])
            ->setAllowedTypes('customer', [Customer::class, 'null'])
            ->setAllowedTypes('customerUser', [CustomerUser::class, 'null'])
            ->setAllowedTypes('allowDelete', 'bool')
            ->setAllowedTypes('deleteConfirmMessage', ['string', 'null'])
            ->setAllowedTypes('allowEdit', 'bool')
            ->setAllowedTypes('allowAdd', 'bool')
            ->setDefaults([
                'customer' => null,
                'customerUser' => null,
                'allowDelete' => false,
                'deleteConfirmMessage' => null,
                'allowEdit' => false,
                'allowAdd' => false,
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

        if ($options['allowAdd'] && $options['customer'] === null) {
            throw new InvalidOptionException('An option "customer" must be provided, when adding is allowed.');
        }

        $deliveryAddresses = [];

        if ($options['customer'] !== null) {
            $deliveryAddresses = $options['customer']->getDeliveryAddresses();
        } elseif ($options['customerUser'] !== null) {
            $deliveryAddresses = $options['customerUser']->getCustomer()->getDeliveryAddresses();
        }
        $view->vars['deliveryAddresses'] = $deliveryAddresses;
        $view->vars['allowDelete'] = $options['allowDelete'];
        $view->vars['deleteConfirmMessage'] = $options['deleteConfirmMessage'];
        $view->vars['allowEdit'] = $options['allowEdit'];
        $view->vars['allowAdd'] = $options['allowAdd'];
        $view->vars['customer'] = $options['customer'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return FormType::class;
    }
}
