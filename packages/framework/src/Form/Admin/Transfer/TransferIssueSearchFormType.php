<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Transfer;

use Shopsys\FrameworkBundle\Model\Transfer\TransferFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferIssueSearchFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transfer\TransferFacade $transferFacade
     */
    public function __construct(
        protected readonly TransferFacade $transferFacade,
    ) {
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
                'placeholder' => t('-- Select name of the transfer --'),
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
