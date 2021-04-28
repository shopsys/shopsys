<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Stock\Stock;
use App\Model\Stock\StockData;
use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class StockFormType extends AbstractType
{
    /**
     * @var \App\Model\Stock\StockFacade
     */
    private $stockFacade;

    private ?Stock $stock;

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

        if ($this->stock !== null) {
            $stockDataBuilder
                ->add('stockId', DisplayOnlyType::class, [
                    'label' => t('ID'),
                    'data' => $this->stock->getId(),
                ])
                ->add('isDefault', DisplayOnlyType::class, [
                    'required' => false,
                    'data' => $this->stock->isDefault() ? t('Yes') : t('No'),
                    'label' => t('Výchozí sklad'),
                ]);
        }

        $stockDataBuilder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Vyplňte prosím název skladu']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Název skladu nesmí být delší než {{ limit }} znaků']),
                ],
                'label' => t('Name'),
            ])
            ->add('isEnabledByDomain', DomainsType::class, [
                'required' => false,
                'label' => t('Display on'),
            ])
            ->add('externalId', TextType::class, [
                'required' => false,
                'label' => t('Externí ID můstku'),
                'constraints' => [
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Externí ID můstku nesmí být delší než {{ limit }} znaků']),
                    new Constraints\Callback([$this, 'sameStockExternalIdValidation']),
                ],
            ])
            ->add('note', TextType::class, [
                'required' => false,
                'label' => t('Interní poznámka'),
            ]);

        $builder->add($stockDataBuilder);
        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
            $context->addViolation('Sklad s tímto externím kódem již existuje');
        }
    }
}
