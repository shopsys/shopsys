<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Stock\Stock;
use App\Model\Stock\StockData;
use App\Model\Stock\StockFacade;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class StockFormType extends AbstractType
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
            'label' => t('Centrální sklad'),
        ])
        ->add(
            'externalId',
            TextType::class,
            [
                'required' => true,
                'label' => t('Externí ID můstku'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Vyplňte prosím externí ID můstku']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Externí ID můstku nesmí být delší než {{ limit }} znaků']),
                    new Constraints\Callback([$this, 'sameStockExternalIdValidation']),
                ],
            ]
        )->add(
            'street',
            TextType::class,
            [
                    'required' => false,
                    'label' => t('Ulice a č. popisné'),
                    'constraints' => [
                        new Constraints\Length(['max' => 100, 'maxMessage' => 'Ulice nesmí být delší než {{ limit }} znaků']),
                    ],
                ]
        )->add(
            'city',
            TextType::class,
            [
                'required' => false,
                'label' => t('Město'),
                'constraints' => [
                    new Constraints\Length(['max' => 100, 'maxMessage' => 'Město nesmí být delší než {{ limit }} znaků']),
                ],
            ]
        )->add(
            'openingHours',
            TextareaType::class,
            [
                'required' => false,
                'label' => t('Otevírací doba'),
            ]
        )->add(
            'extraordinaryOpeningHours',
            TextareaType::class,
            [
                'required' => false,
                'label' => t('Mimořádná otevírací doba'),
            ]
        )->add(
            'contactText1',
            TextType::class,
            [
                'required' => false,
                'label' => t('Kontakt na obchodní dům 1'),
            ]
        )->add(
            'contactText2',
            TextType::class,
            [
                'required' => false,
                'label' => t('Kontakt na obchodní dům 2'),
            ]
        )->add(
            'contactInfo',
            TextType::class,
            [
                'required' => false,
                'label' => t('Mimořádná otevírací doba'),
            ]
        )->add(
            'contactInfo',
            CKEditorType::class,
            [
                'required' => false,
                'label' => t('Informace o obchodním domě'),
            ]
        )->add(
            'locationLat',
            TextType::class,
            [
                'required' => false,
                'label' => t('souřadnice Zeměpisná šířka'),
            ]
        )->add(
            'locationLng',
            TextType::class,
            [
                'required' => false,
                'label' => t('souřadnice Zeměpisná délka'),
            ]
        )->add(
            'image',
            ImageUploadType::class,
            [
               'required' => false,
               'image_entity_class' => Stock::class,
               'image_type' => 'main',
               'file_constraints' => [
                   new Image([
                       'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                       'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                       'maxSize' => '2M',
                       'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                         . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                   ]),
               ],
               'entity' => $options['stock'],
               'label' => t('Upload new image'),
               'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]
        )->add(
            'imageGallery',
            ImageUploadType::class,
            [
               'required' => false,
               'image_entity_class' => Stock::class,
               'image_type' => 'gallery',
               'file_constraints' => [
                   new Image([
                       'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                       'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                       'maxSize' => '2M',
                       'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                         . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                   ]),
               ],
               'entity' => $options['stock'],
               'label' => t('Nahrát nový obrázek galerie'),
               'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
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
