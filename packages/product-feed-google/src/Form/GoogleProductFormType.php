<?php

namespace Shopsys\ProductFeed\GoogleBundle\Form;

use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

class GoogleProductFormType extends AbstractType
{
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('show', MultidomainType::class, [
            'label' => $this->translator->trans('Show in feed'),
            'entry_type' => YesNoType::class,
            'required' => false,
        ])
        ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
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
