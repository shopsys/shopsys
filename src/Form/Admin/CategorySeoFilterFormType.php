<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Category\Category;
use App\Model\CategorySeo\CategorySeoFacade;
use App\Model\CategorySeo\CategorySeoFiltersData;
use Shopsys\FormTypesBundle\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CategorySeoFilterFormType extends AbstractType
{
    /**
     * @var \App\Model\CategorySeo\CategorySeoFacade
     */
    private $categorySeoFacade;

    /**
     * @param \App\Model\CategorySeo\CategorySeoFacade $categorySeoFacade
     */
    public function __construct(
        CategorySeoFacade $categorySeoFacade
    ) {
        $this->categorySeoFacade = $categorySeoFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Model\Category\Category $category */
        $category = $options['category'];

        $domainId = $options['domainId'];

        $builder
            ->add('useFlags', YesNoType::class, [
                'required' => true,
                'label' => t('Dle příznaků'),
                'data' => false,
            ])
            ->add('useOrdering', YesNoType::class, [
                'required' => true,
                'label' => t('Dle řazení'),
                'data' => false,
            ])
            ->add('parameters', ChoiceType::class, [
                'label' => t('Parametry produktů vybrané kategorie'),
                'choices' => $this->categorySeoFacade->getParametersUsedByProductsInCategory($category, $domainId),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => t('Zobrazit kombinace'),
                'attr' => [
                    'class' => 'margin-top-20',
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('category')
            ->setAllowedTypes('category', Category::class)
            ->setRequired('domainId')
            ->setAllowedTypes('domainId', 'int')
            ->setDefaults([
                'data_class' => CategorySeoFiltersData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Callback([$this, 'validate']),
                ],
            ]);
    }

    /**
     * @param CategorySeoFiltersData $categorySeoFiltersData
     * @param ExecutionContextInterface $context
     */
    public function validate(CategorySeoFiltersData $categorySeoFiltersData, ExecutionContextInterface $context): void
    {
        if ($categorySeoFiltersData->useFlags === false && $categorySeoFiltersData->useOrdering === false) {
            $context->buildViolation(t('Prosím vyberte alespoň jedno z příznaků nebo řazení.'))
                ->atPath('useFlags')
                ->addViolation();
        }
    }
}
