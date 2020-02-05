<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Stock\Stock;
use App\Model\Stock\StockData;
use App\Model\Stock\StockFacade;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class StockFormTypeExtension extends AbstractType
{
    /**
     * @var \App\Model\Stock\Stock
     */
    private $stock;

    /**
     * @var \App\Model\Stock\StockFacade
     */
    private $stockFacade;

    /**
     * @param \App\Model\Stock\StockFacade $stockFacade
     */
    public function __construct(StockFacade $stockFacade)
    {
        $this->stockFacade = $stockFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->stock = $options['stock'];

        $stockDataBuilder = $builder->create('stockData', GroupType::class, [
            'label' => t('Stock'),
        ]);

        if ($options['stock'] === null) {
            $stockDataBuilder
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'data' => $options['domain_id'],
                    'label' => t('Domain'),
                ]);
        }

        $stockDataBuilder->add('name', TextType::class, [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Vyplňte prosím název skladu']),
                new Constraints\Length(['max' => 255, 'maxMessage' => 'Název skladu nesmí být delší než {{ limit }} znaků']),
            ],
            'label' => t('Name'),
        ])
        ->add('centralStock', YesNoType::class, [
            'required' => false,
            'label' => t('Central stock'),
        ])
        ->add(
            'externalId',
            TextType::class,
            [
                'required' => true,
                'label' => t('External bridge ID'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Vyplňte prosím external bridge ID']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'External bridge ID skladu nesmí být delší než {{ limit }} znaků']),
                    new Constraints\Callback([$this, 'sameStockExternalIdValidation']),
                ],
            ]
        );

        $builder->add($stockDataBuilder);
        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['stock', 'domain_id'])
            ->setAllowedTypes('stock', [Stock::class, 'null'])
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'data_class' => StockData::class,
                'constraints' => [
                    new Constraints\Callback([$this, 'sameStockNameValidation']),
                ],
            ]);
    }

    /**
     * @param string $externalId
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function sameStockExternalIdValidation(string $externalId, ExecutionContextInterface $context)
    {
        if ($this->stock === null || $externalId !== $this->stock->getExternalId()) {
            $stock = $this->stockFacade->findStockByExternalId($externalId);

            if ($stock !== null) {
                $context->addViolation('Sklad s tímto externím kódem již existuje');
            }
        }
    }

    /**
     * @param \App\Model\Stock\StockData $stockData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function sameStockNameValidation(StockData $stockData, ExecutionContextInterface $context)
    {
        if ($this->stock === null || $stockData->name !== $this->stock->getName()) {
            $stock = $this->stockFacade->findStockByNameAndDomainId($stockData->name, $stockData->domainId);

            if ($stock !== null) {
                $context->buildViolation('Sklad s tímto názvem na této doméně již existuje')->atPath('name')->addViolation();
            }
        }
    }
}
