<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Exception\InvalidOptionException;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DeliveryAddressListType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
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
                'renders_in_own_card' => true,
            ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
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
        $view->vars['showActionColumn'] = $options['allowEdit'] || $options['allowDelete'];
    }
}
