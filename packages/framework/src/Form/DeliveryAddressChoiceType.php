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
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private $currentCustomerUser;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(CurrentCustomerUser $currentCustomerUser)
    {
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
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
     * @param mixed[] $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $deliveryAddresses = [];
        foreach ($this->currentCustomerUser->findCurrentCustomerUser()->getCustomer()->getDeliveryAddresses() as $deliveryAddress) {
            $deliveryAddresses[$deliveryAddress->getId()] = $deliveryAddress;
        }

        $view->vars['deliveryAddresses'] = $deliveryAddresses;
    }

    /**
     * @return string|null
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
