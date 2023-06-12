<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Transfer\TransferFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferIssueSearchFormType extends AbstractType
{
    /**
     * @param \App\Model\Transfer\TransferFacade $transferFacade
     */
    public function __construct(private TransferFacade $transferFacade)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transfers = $this->transferFacade->getAll();

        $builder
            ->add('transfer', ChoiceType::class, [
                'required' => false,
                'choices' => $transfers,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => t('-- Vyberte název přenosu --'),
            ])
            ->add('submit', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
                'method' => 'GET',
            ]);
    }
}
