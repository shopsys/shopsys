<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUserListType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     */
    public function __construct(private readonly CustomerFacade $customerFacade)
    {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['customer'])
            ->setAllowedTypes('customer', [Customer::class])
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

        $view->vars['customerUsers'] = $this->customerFacade->getCustomerUsers($options['customer']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return FormType::class;
    }
}
