<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CustomerUserListType extends AbstractType
{
    public function __construct(private readonly CustomerFacade $customerFacade)
    {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
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

        $view->vars['customerUsers'] = $this->customerFacade->getCustomerUsers($options['customer']);
        $view->vars['allowDelete'] = $options['allowDelete'];
        $view->vars['allowEdit'] = $options['allowEdit'];
        $view->vars['allowAdd'] = $options['allowAdd'];
        $view->vars['deleteConfirmMessage'] = $options['deleteConfirmMessage'];
        $view->vars['customer'] = $options['customer'];
        $view->vars['showActionColumn'] = $options['allowEdit'] || $options['allowDelete'];
    }
}
