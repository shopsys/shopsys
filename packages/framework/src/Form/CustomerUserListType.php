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
            ->setDefined(['allowDelete', 'deleteConfirmMessage', 'allowEdit', 'allowAdd'])
            ->setAllowedTypes('customer', [Customer::class])
            ->setAllowedTypes('allowDelete', 'bool')
            ->setAllowedTypes('allowEdit', 'bool')
            ->setAllowedTypes('allowAdd', 'bool')
            ->setAllowedTypes('deleteConfirmMessage', ['string', 'null'])
            ->setDefaults([
                'mapped' => false,
                'allowDelete' => false,
                'allowEdit' => false,
                'allowAdd' => false,
                'deleteConfirmMessage' => null,
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
        $view->vars['allowDelete'] = $options['allowDelete'];
        $view->vars['allowEdit'] = $options['allowEdit'];
        $view->vars['allowAdd'] = $options['allowAdd'];
        $view->vars['deleteConfirmMessage'] = $options['deleteConfirmMessage'];
        $view->vars['customer'] = $options['customer'];
        $view->vars['showActionColumn'] = $options['allowEdit'] || $options['allowDelete'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return FormType::class;
    }
}
