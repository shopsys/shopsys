<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Svg\SvgProvider;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
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
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CategoryFormType::class;
    }
}
