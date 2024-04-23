<?php

namespace Shopsys\ProductFeed\ZboziBundle\Form;

use Override;
use ReturnTypeWillChange;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Form\Constraints\MoneyRange;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class ZboziProductFormType extends AbstractType
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string $bar
     */
    #[EntityLogIdentify(isLocalized: true)]
    public function foo(string $bar): void
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('show', MultidomainType::class, [
                'label' => $this->translator->trans('Offer in feed'),
                'entry_type' => YesNoType::class,
                'required' => false,
            ])
            ->add('cpc', MultidomainType::class, [
                'label' => $this->translator->trans('Maximum price per click'),
                'entry_type' => MoneyType::class,
                'required' => false,
                'entry_options' => [
                    'currency' => 'CZK',
                    'constraints' => [
                        new MoneyRange([
                            'min' => Money::create(1),
                            'max' => Money::create(500),
                        ]),
                    ],
                ],
            ])
            ->add('cpc_search', MultidomainType::class, [
                'label' => $this->translator->trans('Maximum price per click in offers'),
                'entry_type' => MoneyType::class,
                'required' => false,
                'entry_options' => [
                    'currency' => 'CZK',
                    'constraints' => [
                        new MoneyRange([
                            'min' => Money::create(1),
                            'max' => Money::create(500),
                        ]),
                    ],
                ],
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                // Setting default value of multidomain form "show" to true via event because of dynamic form count
                $multidomainShowForm = $event->getForm()->get('show');
                foreach ($multidomainShowForm as $showForm) {
                    /* @var $showForm \Symfony\Component\Form\FormInterface */
                    if ($showForm->getData() === null) {
                        $showForm->setData(true);
                    }
                }
            });
    }
}
