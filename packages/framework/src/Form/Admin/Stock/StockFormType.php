<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Stock;

use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Model\Stock\StockData;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class StockFormType extends AbstractType
{
    private ?Stock $stock;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade
     */
    public function __construct(
        private readonly StockFacade $stockFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->stock = $options['stock'];

        $stockDataBuilder = $builder->create('stockData', GroupType::class, [
            'label' => t('Warehouse'),
        ]);

        if ($this->stock !== null) {
            $stockDataBuilder
                ->add('stockId', DisplayOnlyType::class, [
                    'label' => t('ID'),
                    'data' => $this->stock->getId(),
                ])
                ->add('isDefault', DisplayOnlyType::class, [
                    'required' => false,
                    'data' => $this->stock->isDefault() ? t('Yes') : t('No'),
                    'label' => t('Default warehouse'),
                ]);
        }

        $stockDataBuilder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter warehouse name']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Warehouse name cannot be longer than {{ limit }} characters']),
                ],
                'label' => t('Name'),
            ])
            ->add('isEnabledByDomain', DomainsType::class, [
                'required' => false,
                'label' => t('Display on'),
            ])
            ->add('externalId', TextType::class, [
                'required' => false,
                'label' => t('External bridge ID'),
                'constraints' => [
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'External bridge ID cannot be longer than {{ limit }} characters']),
                    new Constraints\Callback([$this, 'sameStockExternalIdValidation']),
                ],
            ])
            ->add('note', TextType::class, [
                'required' => false,
                'label' => t('Internal note'),
            ]);

        $builder->add($stockDataBuilder);
        $builder->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['stock'])
            ->setAllowedTypes('stock', [Stock::class, 'null'])
            ->setDefaults([
                'data_class' => StockData::class,
            ]);
    }

    /**
     * @param string|null $externalId
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
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
