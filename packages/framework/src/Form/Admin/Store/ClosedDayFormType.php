<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Store;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayData;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ClosedDayFormType extends AbstractType
{
    public function __construct(
        protected readonly StoreFacade $storeFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $domainId = $options['data']->domainId;
        $builder
            ->add('isPublicHoliday', YesNoType::class, [
                'label' => 'Public holiday',
                'help' => t('Public holidays are taken into account when calculating the order withdrawal deadline.'),
            ])
            ->add('date', DatePickerType::class, [
                'required' => true,
                'label' => 'Date',
                'widget' => 'single_text',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter date']),
                ],
                'view_timezone' => null,
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Name',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters']),
                ],
            ])
            ->add('excludedStores', ChoiceType::class, [
                'required' => false,
                'label' => 'Excluded stores',
                'choices' => $this->storeFacade->getStoresByDomainId($domainId),
                'choice_label' => static fn (Store $store) => $store->getName(),
                'multiple' => true,
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_closedday_list',
                'entity' => $options['closed_day'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('closed_day')
            ->setAllowedTypes('closed_day', [ClosedDay::class, 'null'])
            ->setDefaults([
                'closed_day' => null,
                'data_class' => ClosedDayData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
