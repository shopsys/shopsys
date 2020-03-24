<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Form\Admin\Advert\AdvertFormType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class AdvertFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildVisibilityIntervalForm($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildVisibilityIntervalForm(FormBuilderInterface $builder): void
    {
        $builder->add('datetimeVisibleFrom', DatePickerType::class, [
            'view_timezone' => DateTimeHelper::UTC_TIMEZONE,
            'required' => false,
            'label' => t('Datum zobrazení OD'),
        ])->add('datetimeVisibleTo', DatePickerType::class, [
            'view_timezone' => DateTimeHelper::UTC_TIMEZONE,
            'required' => false,
            'label' => t('Datum zobrazení DO'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield AdvertFormType::class;
    }
}
