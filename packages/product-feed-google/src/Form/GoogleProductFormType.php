<?php

namespace Shopsys\ProductFeed\GoogleBundle\Form;

use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class GoogleProductFormType extends AbstractType
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
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('show', MultidomainType::class, [
            'label' => $this->translator->trans('Show in feed'),
            'entry_type' => YesNoType::class,
            'required' => false,
        ])
        ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            // Setting default value of multidomain form "show" to true via event because of dynamic form count
            $multidomainShowForm = $event->getForm()->get('show');
            /** @var \Symfony\Component\Form\FormInterface $showForm */
            foreach ($multidomainShowForm as $showForm) {
                if ($showForm->getData() === null) {
                    $showForm->setData(true);
                }
            }
        });
    }
}
