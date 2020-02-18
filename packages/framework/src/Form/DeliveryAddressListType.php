<?php

namespace Shopsys\FrameworkBundle\Form;

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
        $resolver->setRequired('customerUser')
            ->setAllowedTypes('customerUser', CustomerUser::class)
            ->setDefaults([
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
        /** @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress[] $options['customerUser'] */
        $view->vars['deliveryAddresses'] = $options['customerUser']->getCustomer()->getDeliveryAddresses();
    }

    /**
     * @return null|string
     */
    public function getParent()
    {
        return FormType::class;
    }
}
