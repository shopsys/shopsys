<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Store;

use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayData;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ClosedDayFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     */
    public function __construct(
        protected readonly StoreFacade $storeFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $domainId = $options['data']->domainId;
        $builder
            ->add('date', DatePickerType::class, [
                'required' => true,
                'input' => 'datetime_immutable',
                'label' => t('Date'),
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'full-width',
                ],
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter date']),
                ],
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'label' => t('Name'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters']),
                ],
            ])
            ->add('excludedStores', ChoiceType::class, [
                'required' => false,
                'label' => t('Excluded stores'),
                'choices' => $this->storeFacade->getStoresByDomainId($domainId),
                'choice_label' => static fn (Store $store) => $store->getName(),
                'multiple' => true,
                'attr' => [
                    'class' => 'js-role-group-select',
                ],
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClosedDayData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
