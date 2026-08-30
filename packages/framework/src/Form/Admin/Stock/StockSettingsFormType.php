<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Stock;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\MessageType;
use Shopsys\FrameworkBundle\Model\Stock\StockSettingsData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Twig\Environment;

final class StockSettingsFormType extends AbstractType
{
    public function __construct(
        protected readonly Environment $environment,
        protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderStockSettingGroup = $builder->create('stockSettings', GroupType::class, [
            'label' => 'Warehouse settings',
        ]);

        $builderStockSettingGroup
            ->add('transfer', TextType::class, [
                'label' => 'Days for transfer between warehouses',
                'help' => t(
                    'Calendar days needed to move the goods to the store warehouse when the store does not have enough in stock (or has no warehouse assigned). They are added to the expected personal pickup date; when the resulting day is not a delivery day of the transport or the store is closed, the date is moved to the nearest allowed day.',
                ),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Regex(pattern: '/^\d+$/'),
                    new Constraints\GreaterThanOrEqual(value: 0),
                ],
            ]);

        $builderFeedSettingGroup = $builder->create('feedSettings', GroupType::class, [
            'label' => 'XML feeds settings',
        ]);

        $builderFeedSettingGroup
            ->add('feedDeliveryDaysForOutOfStockProducts', IntegerType::class, [
                'label' => 'Number of delivery days for out of stock products in XML feeds',
                'required' => true,
                'constraints' => [
                    new Constraints\NotNull(
                        message: 'Please enter the number of delivery days.',
                    ),
                ],
            ])
            ->add('feedDeliveryDaysForOutOfStockProductsInfo', MessageType::class, [
                'message_level' => MessageType::MESSAGE_LEVEL_INFO,
                'data' => $this->environment->render('@ShopsysAdministration/content/feed/feedDeliveryDaysForOutOfStockProductsInfo.html.twig'),
            ]);

        $builder
            ->add($builderStockSettingGroup)
            ->add($builderFeedSettingGroup)
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save warehouse settings'),
            ]);

        $this->pluginCrudExtensionFacade->extendForm($builder, 'stockSettings', 'pluginData');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => StockSettingsData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
