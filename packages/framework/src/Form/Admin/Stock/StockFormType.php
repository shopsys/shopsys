<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Stock;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Model\Stock\StockData;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class StockFormType extends AbstractType
{
    private ?Stock $stock;

    public function __construct(
        private readonly StockFacade $stockFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->stock = $options['stock'];

        $stockDataBuilder = $builder->create('stockData', GroupType::class, [
            'label' => 'Warehouse',
        ]);

        if ($this->stock !== null) {
            $stockDataBuilder
                ->add('stockId', DisplayOnlyType::class, [
                    'label' => 'ID',
                    'data' => $this->stock->getId(),
                ])
                ->add('isDefault', DisplayOnlyType::class, [
                    'required' => false,
                    'data' => $this->stock->isDefault() ? t('Yes') : t('No'),
                    'label' => 'Default warehouse',
                ]);
        }

        $stockDataBuilder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter warehouse name']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Warehouse name cannot be longer than {{ limit }} characters']),
                ],
                'label' => 'Name',
            ])
            ->add('isEnabledByDomain', DomainsType::class, [
                'required' => false,
                'label' => 'Display on',
            ])
            ->add('externalId', TextType::class, [
                'required' => false,
                'label' => 'External bridge ID',
                'constraints' => [
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'External bridge ID cannot be longer than {{ limit }} characters']),
                    new Constraints\Callback([$this, 'sameStockExternalIdValidation']),
                ],
            ])
            ->add('note', TextType::class, [
                'required' => false,
                'label' => 'Internal note',
            ]);

        $builder->add($stockDataBuilder);
        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_stock_list',
            'entity' => $options['stock'],
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['stock'])
            ->setAllowedTypes('stock', [Stock::class, 'null'])
            ->setDefaults([
                'data_class' => StockData::class,
            ]);
    }

    public function sameStockExternalIdValidation(?string $externalId, ExecutionContextInterface $context): void
    {
        if ($externalId === null) {
            return;
        }

        if ($this->stock !== null && $externalId === $this->stock->getExternalId()) {
            return;
        }

        $stock = $this->stockFacade->findStockByExternalId($externalId);

        if ($stock !== null) {
            $context->addViolation('Warehouse with this external code already exists');
        }
    }
}
