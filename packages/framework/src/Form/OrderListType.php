<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderListType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        private readonly OrderFacade $orderFacade,
        private readonly Localization $localization,
    ) {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['customer', 'limit'])
            ->setAllowedTypes('customer', Customer::class)
            ->setAllowedTypes('limit', 'int')
            ->setDefaults([
                'mapped' => false,
                'limit' => 10,
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['orders'] = $this->orderFacade->getLastCustomerOrdersByLimit($options['customer'], $options['limit'], $this->localization->getAdminLocale());
        $view->vars['customer'] = $options['customer'];
        $view->vars['limit'] = $options['limit'];
    }
}
