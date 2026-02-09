<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PriceList;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\BasicFileUploadType;
use Shopsys\FrameworkBundle\Form\Constraints\MustUploadFile;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Model\PriceList\PriceList;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListCsvColumnsEnum;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class ImportPriceListFormType extends AbstractType
{
    public function __construct(
        protected readonly PriceListFacade $priceListFacade,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly Domain $domain,
        protected readonly PriceListCsvColumnsEnum $priceListCsvColumnsEnum,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('selectPriceList', ChoiceType::class, [
                'choices' => $this->priceListFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'group_by' => function (PriceList $priceList) {
                    return $this->domain->getDomainConfigById($priceList->getDomainId())->getName();
                },
                'required' => false,
                'placeholder' => '-- Create new price list --',
                'label' => 'Overwrite Price list',
                'attr' => [
                    'class' => 'js-import-price-list-select-list',
                    'data-load-metadata-url' => $this->urlGenerator->generate('admin_pricelist_loadmetadata', ['id' => '0']),
                ],
            ])
            ->add('domainId', DomainType::class, [
                'required' => true,
                'label' => 'Domain',
                'row_attr' => [
                    'data-js-import-price-list-domain-id' => null,
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter product list name'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Product list name cannot be longer than {{ limit }} characters',
                    ),
                ],
                'attr' => [
                    'class' => 'js-import-price-list-name',
                ],
            ])
            ->add('validFrom', DateTimeType::class, [
                'label' => 'Valid from',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter valid from date'),
                ],
                'attr' => [
                    'class' => 'js-import-price-list-valid-from',
                ],
            ])
            ->add('validTo', DateTimeType::class, [
                'label' => 'Valid to',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter valid to date'),
                ],
                'attr' => [
                    'class' => 'js-import-price-list-valid-to',
                ],
            ])
            ->add('csvFile', BasicFileUploadType::class, [
                'required' => true,
                'constraints' => [
                    new MustUploadFile(message: 'Please upload a CSV file'),
                ],
                'info_text' => t('CSV file must be in UTF-8 encoding with columns "{{ columns }}". A comma is recommended as a column delimiter and a dot (.) as a decimal separator.', [
                    '{{ columns }}' => implode('", "', $this->priceListCsvColumnsEnum->getAllCases()),
                ]),
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '2M',
                        extensions: ['csv' => ['text/csv', 'text/plain']],
                        maxSizeMessage: 'Uploaded file is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an file is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'label' => 'CSV file',
            ]);

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_pricelist_list',
            'save_label' => t('Import'),
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Constraints\Callback(callback: [$this, 'checkDateValidity']),
                ],
            ]);
    }

    public function checkDateValidity(array $priceListData, ExecutionContextInterface $context): void
    {
        if (!array_key_exists('validTo', $priceListData) || !array_key_exists('validFrom', $priceListData)) {
            return;
        }

        if ($priceListData['validTo'] < $priceListData['validFrom']) {
            $context->buildViolation('"Valid to" must be greater than "Valid from"')
                ->atPath('validTo')
                ->addViolation();
        }
    }
}
