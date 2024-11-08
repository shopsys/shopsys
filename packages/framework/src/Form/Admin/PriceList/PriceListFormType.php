<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PriceList;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Model\PriceList\PriceList;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListData;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PriceListFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $priceList = $options['priceList'];

        if ($priceList instanceof PriceList) {
            $builder
                ->add('id', DisplayOnlyType::class, [
                    'label' => t('ID'),
                    'data' => $priceList->getId(),
                ])
                ->add('lastUpdate', DisplayOnlyType::class, [
                    'label' => t('Last update'),
                    'data' => $this->dateTimeFormatterExtension->formatDateTime($priceList->getLastUpdate()),
                ]);
        }

        $this->addDomainIconField($builder, $priceList);

        $builder
            ->add('name', TextType::class, [
                'label' => t('Name'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter product list name']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Product list name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('validFrom', DateTimeType::class, [
                'label' => t('Valid from'),
                'input' => 'datetime_immutable',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter valid from date']),
                ],
            ])
            ->add('validTo', DateTimeType::class, [
                'label' => t('Valid to'),
                'input' => 'datetime_immutable',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter valid to date']),
                ],
            ]);

        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('priceList')
            ->setAllowedTypes('priceList', [PriceList::class, 'null'])
            ->setDefaults([
                'data_class' => PriceListData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Constraints\Callback([$this, 'checkDateValidity']),
                ],
            ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListData $priceListData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function checkDateValidity(PriceListData $priceListData, ExecutionContextInterface $context): void
    {
        if ($priceListData->validTo < $priceListData->validFrom) {
            $context->buildViolation(t('"Valid to" must be greater than "Valid from"', [], Translator::VALIDATOR_TRANSLATION_DOMAIN))
                ->atPath('validTo')
                ->addViolation();
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceList|null $priceList
     */
    private function addDomainIconField(FormBuilderInterface $builder, ?PriceList $priceList): void
    {
        if (!$this->domain->isMultidomain()) {
            return;
        }

        if ($priceList instanceof PriceList) {
            $builder->add('domainIcon', DisplayOnlyDomainIconType::class, [
                'label' => t('Domain'),
                'data' => $priceList->getDomainId(),
            ]);
        } else {
            $builder->add('domainId', DomainType::class, [
                'required' => true,
                'label' => t('Domain'),
            ]);
        }
    }
}
