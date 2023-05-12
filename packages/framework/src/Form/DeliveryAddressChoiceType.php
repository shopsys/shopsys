<?php

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Form\Constraints\DeliveryAddressOfCurrentCustomer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryAddressChoiceType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(private readonly CurrentCustomerUser $currentCustomerUser)
    {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'choice_value' => 'id',
                'multiple' => false,
                'expanded' => true,
                'required' => true,
                'choices' => $this->currentCustomerUser->findCurrentCustomerUser()->getCustomer()->getDeliveryAddresses(),
                'constraints' => [
                    new DeliveryAddressOfCurrentCustomer(),
                ],
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

        $deliveryAddresses = [];

        foreach ($this->currentCustomerUser->findCurrentCustomerUser()->getCustomer()->getDeliveryAddresses() as $deliveryAddress) {
            $deliveryAddresses[$deliveryAddress->getId()] = $deliveryAddress;
        }

        $view->vars['deliveryAddresses'] = $deliveryAddresses;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
