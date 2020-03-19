<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Svg\SvgProvider;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \App\Model\Svg\SvgProvider
     */
    private $svgProvider;

    /**
     * @param \App\Model\Svg\SvgProvider $svgProvider
     */
    public function __construct(SvgProvider $svgProvider)
    {
        $this->svgProvider = $svgProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $settingsBuilder = $builder->get('settings');

        $settingsBuilder
            ->add('svgIcon', ChoiceType::class, [
                'label' => t('Nastavení SVG ikony'),
                'required' => false,
                'choices' => $this->svgProvider->getAllSvgIconsNames(),
                'choices_as_values' => true,
            ]);

        /** @var \Ivory\OrderedForm\Builder\OrderedFormBuilder $builderShortDescriptionGroup */
        $builderShortDescriptionGroup = $builder->create('shortDescriptionGroup', GroupType::class, [
            'label' => t('Krátký popis'),
        ]);

        $builderShortDescriptionGroup->add('shortDescription', MultidomainType::class, [
            'entry_type' => CKEditorType::class,
            'required' => false,
            'display_format' => FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING,
        ]);

        $builder->add($builderShortDescriptionGroup);

        $builderShortDescriptionGroup->setPosition(['after' => 'seo']);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CategoryFormType::class;
    }
}
